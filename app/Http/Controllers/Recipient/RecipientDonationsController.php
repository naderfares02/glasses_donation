<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\DeliveryConfirmation;
use App\Models\DonationReceipt;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\AdminRecipientConfirmedDeliveryNotification;
use App\Notifications\AdminRecipientDeniedDeliveryNotification;
use App\Notifications\DonorDonationApprovedNotification;
use App\Notifications\DonorRecipientDeniedDeliveryNotification;
use Barryvdh\DomPDF\Facade\Pdf;

class RecipientDonationsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending'); 

        if (!in_array($tab, ['pending', 'received', 'not_received'])) {
            $tab = 'pending';
        }

        $confirmations = DeliveryConfirmation::query()
            ->where('recipient_id', auth()->id())
            ->where('status', $tab)
            ->with([
                'donationRequest.glasses.primaryImage',
                'donationRequest.donor:id,name,avatar',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $counts = DeliveryConfirmation::query()
            ->where('recipient_id', auth()->id())
            ->selectRaw("
                SUM(status='pending') as pending_count,
                SUM(status='received') as received_count,
                SUM(status='not_received') as not_received_count
            ")
            ->first();

        return view('recipient.donations.index', compact('confirmations', 'tab', 'counts'));
    }

public function show(DeliveryConfirmation $confirmation)
{
    abort_if($confirmation->recipient_id !== auth()->id(), 403);
    $confirmation->load([
        'donationRequest.glasses.primaryImage',
        'donationRequest.donor:id,name,avatar',
    ]);

    return view('recipient.donations.show', compact('confirmation'));
}

public function markReceived(Request $request, DeliveryConfirmation $confirmation)
{
    abort_if($confirmation->recipient_id !== auth()->id(), 403);

    if (!in_array($confirmation->status, ['pending', 'not_received'], true)) {
        return back()->with('error', 'This request has already been resolved.');
    }

    $confirmation->loadMissing('donationRequest');
    if ($confirmation->donationRequest?->status === 'rejected') {
        return back()->with('error', 'This donation request has already been closed by the admin.');
    }

    $data = $request->validate([
        'recipient_note' => ['nullable', 'string', 'max:2000'],
    ]);

    $donationRequest = $confirmation->donationRequest;

    $requireAdminApproval = (bool) setting('donations.require_admin_approval_for_donated', true);

    $receipt = null;

    DB::transaction(function () use ($confirmation, $data, $donationRequest, $requireAdminApproval, &$receipt) {
        $confirmation->update([
            'status' => 'received',
            'recipient_note' => $data['recipient_note'] ?? null,
            'recipient_responded_at' => now(),
        ]);

        if ($donationRequest && ! $requireAdminApproval) {
            $locked = DonationRequest::whereKey($donationRequest->id)
                ->lockForUpdate()
                ->first();

            // Receipts are only auto-issued here when the admin approval
            // workflow is disabled. When it's enabled, an admin still has
            // to review and approve the request before a receipt exists.
            if ($locked && $locked->status === 'approved' && ! $locked->receipt) {

                if ($locked->glasses) {
                    $locked->glasses->update(['status' => 'donated']);
                }

                ContactRequest::where('glasses_id', $locked->glasses_id)
                    ->whereIn('status', ['pending', 'accepted', 'on_hold'])
                    ->update(['status' => 'closed']);

                $receipt = DonationReceipt::create([
                    'donation_request_id' => $locked->id,
                    'glasses_id' => $locked->glasses_id,
                    'donor_id' => $locked->donor_id,
                    'recipient_id' => $locked->recipient_id,
                    'approved_by' => null,
                    'delivered_date' => $locked->delivered_date,
                    'admin_note' => null,
                    'receipt_code' => 'RCPT-'.strtoupper(Str::random(10)),
                    'issued_at' => now(),
                ]);
            }
        }
    });

    if ($receipt) {
        try {
            $pdf = Pdf::loadView('receipts.pdf', [
                'receipt' => $receipt->loadMissing(['donor', 'recipient', 'glasses', 'approver']),
                'request' => $donationRequest,
            ])->setPaper('a4');

            $path = "receipts/{$receipt->receipt_code}.pdf";
            Storage::disk('local')->put($path, $pdf->output());
            $receipt->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            report($e);
        }

        $donationRequest->loadMissing('donor');

        try {
            $donationRequest->donor?->notify(new DonorDonationApprovedNotification($donationRequest));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('recipient.donations.index', ['tab' => 'received'])
            ->with('success', 'Thanks! Your confirmation was recorded and a donation receipt has been issued.');
    }

    if ($donationRequest) {
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        Notification::send($admins, new AdminRecipientConfirmedDeliveryNotification($donationRequest));
    }

    return redirect()
        ->route('recipient.donations.index', ['tab' => 'received'])
        ->with('success', 'Thanks! Marked as received.');
}

    public function markNotReceived(Request $request, DeliveryConfirmation $confirmation)
    {
        abort_if($confirmation->recipient_id !== auth()->id(), 403);

        if ($confirmation->status !== 'pending') {
            return back()->with('error', 'This request is not pending.');
        }

        $data = $request->validate([
            'recipient_note' => ['required', 'string', 'max:2000'],
        ]);

        $confirmation->update([
            'status' => 'not_received',
            'recipient_note' => $data['recipient_note'],
            'recipient_responded_at' => now(),
        ]);

        $donationRequest = $confirmation->donationRequest;

        if ($donationRequest) {
            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
            Notification::send($admins, new AdminRecipientDeniedDeliveryNotification($donationRequest));

            $donationRequest->loadMissing('donor');
            $donationRequest->donor->notify(new DonorRecipientDeniedDeliveryNotification($donationRequest));
        }

        return redirect()
            ->route('recipient.donations.index', ['tab' => 'not_received'])
            ->with('success', 'Marked as not received.');
    }
}