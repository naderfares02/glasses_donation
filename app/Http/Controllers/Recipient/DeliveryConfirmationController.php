<?php

// app/Http/Controllers/Recipient/DeliveryConfirmationController.php
namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfirmation;
use Illuminate\Http\Request;
use App\Notifications\AdminRecipientConfirmedDeliveryNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Models\DonationRequest;
use Illuminate\Support\Facades\DB;

class DeliveryConfirmationController extends Controller
{
    public function show(DeliveryConfirmation $confirmation)
    {
        // أمان: فقط المستفيد صاحب الطلب
        abort_if($confirmation->recipient_id !== auth()->id(), 403);

        $confirmation->load([
            'glasses.primaryImage',
            'donor:id,name,avatar',
            'recipient:id,name,avatar',
        ]);


        return view('recipient.delivery_confirmations.show', compact('confirmation'));
    }

public function confirmReceived(Request $request, DeliveryConfirmation $confirmation)
{
    abort_if($confirmation->recipient_id !== auth()->id(), 403);

    if ($confirmation->status !== 'pending') {
        return back()->with('error', 'This request is already resolved.');
    }

    $data = $request->validate([
        'recipient_note' => ['nullable', 'string', 'max:2000'],
    ]);

    $donationRequest = $confirmation->donationRequest;
    if (!$donationRequest) {
        return back()->with('error', 'Donation request not found.');
    }

    DB::transaction(function () use ($confirmation, $data) {
        $confirmation->update([
            'status' => 'received', // ✅ ليست received
            'recipient_note' => $data['recipient_note'] ?? null,
            'recipient_responded_at' => now(),
        ]);
    });

    // ✅ أرسل بعد transaction
    $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
    $admins->Notify( new AdminRecipientConfirmedDeliveryNotification($donationRequest));

    return back()->with('success', 'Thanks. Your response was sent to admin for review.');
}
    public function denyReceived(Request $request, DeliveryConfirmation $confirmation)
{
    abort_if($confirmation->recipient_id !== auth()->id(), 403);

    if ($confirmation->status !== 'pending') {
        return back()->with('error', 'This request is already resolved.');
    }

    $data = $request->validate([
        'recipient_note' => ['nullable', 'string', 'max:2000'],
    ]);

    $donationRequest = $confirmation->donationRequest;
    if (!$donationRequest) {
        return back()->with('error', 'Donation request not found.');
    }

    DB::transaction(function () use ($confirmation, $data) {
        $confirmation->update([
            'status' => 'not_received',
            'recipient_note' => $data['recipient_note'] ?? null,
            'recipient_responded_at' => now(),
        ]);
    });

    $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
    $admins->Notify( new AdminRecipientConfirmedDeliveryNotification($donationRequest));

    return back()->with('success', 'Your response was sent to admin for review.');
}
}