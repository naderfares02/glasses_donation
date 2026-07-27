<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfirmation;
use Illuminate\Http\Request;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminRecipientConfirmedDeliveryNotification;

class RecipientDonationsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending'); // pending | received | not_received

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

    public function show(DeliveryConfirmation $confirmation,glasses $item)
    {
        abort_if($confirmation->recipient_id !== auth()->id(), 403);

        $confirmation->load([
            'donationRequest.glasses.primaryImage',
            'donationRequest.donor:id,name,avatar',
        ]);

        $item->load(['primaryImage', 'images']);

        return view('recipient.donations.show', compact('confirmation','item'));
    }

    public function markReceived(Request $request, DeliveryConfirmation $confirmation)
{
    abort_if($confirmation->recipient_id !== auth()->id(), 403);

  if ($confirmation->status !== 'pending') {
        return back()->with('error', 'This request has already been resolved.');
    }

    $data = $request->validate([
        'recipient_note' => ['nullable', 'string', 'max:2000'],
    ]);

    // 1) حدّث حالة التأكيد
    $confirmation->update([
        'status' => 'received',
        'recipient_note' => $data['recipient_note'] ?? null,
        'confirmed_at' => now(),
        'denied_at' => null,
    ]);

    // 2) جيب DonationRequest المرتبط (ضروري للإشعار)
    $donationRequest = $confirmation->donationRequest; // لازم تكون العلاقة موجودة

    if ($donationRequest) {
        // 3) جيب الأدمنز
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        // 4) أرسل إشعار
        Notification::send($admins, new AdminRecipientConfirmedDeliveryNotification($donationRequest));}

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
            'recipient_note' => ['required', 'string', 'max:2000'], // خليها required عند الرفض
        ]);

        $confirmation->update([
            'status' => 'not_received',
            'recipient_note' => $data['recipient_note'],
            'denied_at' => now(),
            'confirmed_at' => null,
        ]);

        // (اختياري) هنا لاحقاً: إشعار للأدمن/المتبرع أن المستفيد نفى

        return redirect()
            ->route('recipient.donations.index', ['tab' => 'not_received'])
            ->with('success', 'Marked as not received.');
    }
}