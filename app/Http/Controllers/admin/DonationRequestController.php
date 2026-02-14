<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfirmation;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\DonorDonationApprovedNotification;
use App\Notifications\DonorDonationRejectedNotification;


class DonationRequestController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $requests = DonationRequest::query()
            ->with([
                'glasses.primaryImage',
                'donor:id,name,email,avatar',
                'recipient:id,name,email,avatar',
            ])
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($q, function ($qq) use ($q) {
                $qq->whereHas('glasses', fn($g) => $g->where('title', 'like', "%{$q}%"))
                   ->orWhereHas('donor', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email','like',"%{$q}%"))
                   ->orWhereHas('recipient', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email','like',"%{$q}%"));
            })
            ->orderByRaw("CASE 
                WHEN status='pending' THEN 0
                WHEN status='approved' THEN 1
                WHEN status='rejected' THEN 2
                ELSE 3 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.donation_requests.index', compact('requests'));
    }

    public function show(DonationRequest $donationRequest)
    {
        $donationRequest->load([
            'glasses.primaryImage',
            'donor:id,name,email,avatar',
            'recipient:id,name,email,avatar',
            'deliveryConfirmation',
            // 'conversation.messages.sender', // إذا بدك تعرض جزء من المحادثة هنا
        ]);

        return view('admin.donation_requests.show', compact('donationRequest'));
    }

    public function approve(Request $request, DonationRequest $donationRequest, DeliveryConfirmation $deliveryConfirmation)
{
    // يسمح بالموافقة حتى لو كان مرفوض سابقاً
    if (!in_array($donationRequest->status, ['pending', 'rejected'])) {
        return back()->with('error', 'This request cannot be approved in its current status.');
    }

    $data = $request->validate([
        'admin_note' => ['nullable', 'string', 'max:2000'],
    ]);

    DB::transaction(function () use ($donationRequest, $data) {

        $donationRequest->update([
            'status'      => 'approved',
            'admin_note'  => $data['admin_note'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // ✅ بعد الموافقة: حدّث النظارة إلى donated (أو أي منطق عندك)
        $donationRequest->glasses()->update([
            'status' => 'donated',
        ]);

        $confirmation = $donationRequest->deliveryConfirmation();


        if ($confirmation){
            $confirmation->update([
                'status' => 'received',
                'updated_at' => now(),
            ]);
        }

        $donationRequest->loadMissing('donor'); // ضروري

        if ($donationRequest->donor) {
            $donationRequest->donor->notify(new DonorDonationApprovedNotification($donationRequest));
        }

    });

    return back()->with('success', 'Donation request approved.');
}

    public function reject(Request $request, DonationRequest $donationRequest)
    {
        $data = $request->validate([
            'admin_note' => ['required','string','max:2000'],
        ]);

        if ($donationRequest->status !== 'pending') {
            return back()->with('error', 'This request is not pending.');
        }

        DB::transaction(function () use ($donationRequest, $data) {
            $donationRequest->update([
                'status' => 'rejected',
                'admin_note' => $data['admin_note'],
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            $confirmation = $donationRequest->deliveryConfirmation();

            if ($confirmation){
            $confirmation->update([
                'status' => 'not_received',
                'updated_at' => now(),
            ]);
        }

            // ❗عند الرفض: ارجع النظارة متاحة (أو خلّيها in_contact حسب نظامك)
            $donationRequest->glasses->update([
                'status' => 'reserved',
                'active_contact_request_id' => null,
            ]);
        });

        $donationRequest->loadMissing('donor');

        if ($donationRequest->donor) {
            $donationRequest->donor->notify(new DonorDonationRejectedNotification($donationRequest));
        }

        return redirect()->route('admin.donation_requests.index')
            ->with('success', 'Donation request rejected.');
    }
}