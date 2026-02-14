<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Glasses;
use App\Models\ContactRequest;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;
use App\Models\DonationRequest;
use App\Models\DeliveryConfirmation;
use App\Notifications\RecipientMustConfirmDeliveryNotification;

class DonorContactRequestController extends Controller
{
    public function index(Glasses $glasses)
{
    abort_if($glasses->user_id !== auth()->id(), 403);

    $requests = $glasses->contactRequests()
    ->with(['recipient', 'conversation'])
    ->orderByRaw("
        CASE status
            WHEN 'accepted' THEN 1
            WHEN 'pending' THEN 2
            WHEN 'on_hold' THEN 3
            WHEN 'rejected' THEN 4
            WHEN 'closed' THEN 5
        END
    ")
    ->orderByDesc('created_at')
    ->get();



    return view('donor.requests.index', compact('glasses','requests'));
}


public function accept(ContactRequest $request)
{
    $request->load('glasses');

    abort_if($request->donor_id !== auth()->id(), 403);

    if ($request->status !== 'pending') {
        return back()->with('error', 'This request is not pending.');
    }

    DB::transaction(function () use ($request) {

        $glasses = Glasses::whereKey($request->glasses_id)
            ->lockForUpdate()
            ->firstOrFail();

        // لازم تكون متاحة
        if ($glasses->status !== 'available') {
            abort(409, 'Glasses is not available.');
        }

        // إذا في طلب نشط بالفعل -> ممنوع
        if ($glasses->active_contact_request_id) {
            abort(409, 'Glasses already has an active contact request.');
        }

        $request->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        ContactRequest::where('glasses_id', $glasses->id)
            ->where('id', '!=', $request->id)
            ->where('status', 'pending')
            ->update(['status' => 'on_hold']);

        $glasses->update([
            'status' => 'reserved',
            'active_contact_request_id' => $request->id,
        ]);

        Conversation::firstOrCreate(
            ['contact_request_id' => $request->id],
            [
                'glasses_id' => $glasses->id,
                'donor_id' => $request->donor_id,
                'recipient_id' => $request->recipient_id,
                'status' => 'open',
            ]
        );
    });

    return back()->with('success', 'Request accepted.');
}



public function disconnect(Conversation $conversation)
{
    abort_if($conversation->donor_id !== auth()->id(), 403);

    // ✅ حمّل العلاقات لتفادي null
    $conversation->loadMissing(['request.glasses']);

    $glasses = $conversation->request?->glasses;

    // إذا ما في request/glasses (حالة نادرة) اقفل المحادثة فقط
    $conversation->update(['status' => 'closed']);

    if ($conversation->request) {
        $conversation->request->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    if ($glasses && $glasses->status !== 'donated') {
        $glasses->update([
            'status' => 'available',
            'active_contact_request_id' => null,
        ]);
    }

    ContactRequest::where('glasses_id', $glasses->id)
    ->where('status', 'on_hold')
    ->update(['status' => 'pending']);

    // ✅ الأفضل: توجيه لصفحة inbox وفتح نفس المحادثة
    return redirect()
        ->route('donor.chats.index', ['conversation' => $conversation->id])
        ->with('success', 'Connection closed. Glasses is available again.');
}



public function reject(ContactRequest $request)
{
    abort_if($request->donor_id !== auth()->id(), 403);

    $request->update(['status' => 'rejected']);

    return back()->with('success','Request rejected.');
}


public function markDonated(Request $request, Glasses $glasses)
{
    abort_if($glasses->user_id !== auth()->id(), 403);

    $data = $request->validate([
        'delivered_date' => ['nullable', 'date'],
        'donor_note'     => ['nullable', 'string', 'max:2000'],
    ]);

    // المحادثة المفتوحة الحالية لهذه النظارة
    $conversationId = $request->input('conversation_id');

    $conversation = Conversation::where('id', $conversationId)
        ->where('glasses_id', $glasses->id)
        ->where('donor_id', auth()->id())
        ->where('status', 'open')
        ->first();

    if (!$conversation) {
        return back()->with('error', 'No open conversation found for this glasses.');
    }

    $conversation->loadMissing('recipient');

    [$donationRequest, $confirmation] = DB::transaction(function () use ($glasses, $conversation, $data) {

        // ✅ اقفل Row النظارة لمنع ضغطتين/تضارب
        $lockedGlasses = Glasses::where('id', $glasses->id)->lockForUpdate()->first();

        // (اختياري) لو النظارة أصلاً pending_donation لا تعيد إرسال
        // if ($lockedGlasses->status === 'pending_donation') {
        //     abort(409, 'This glasses already has a pending donation request.');
        // }

        // ✅ طلب التبرع: واحد pending فقط لكل نظارة
        $donationRequest = DonationRequest::updateOrCreate(
            [
                'glasses_id' => $lockedGlasses->id,
                'status'     => 'pending',
            ],
            [
                'conversation_id' => $conversation->id,
                'donor_id'        => $conversation->donor_id,
                'recipient_id'    => $conversation->recipient_id,
                'delivered_date'  => $data['delivered_date'] ?? null,
                'donor_note'      => $data['donor_note'] ?? null,
            ]
        );

                $confirmation = DeliveryConfirmation::updateOrCreate(
    [
        'donation_request_id' => $donationRequest->id,
    ],
    [
        'conversation_id' => $donationRequest->conversation_id,
        'glasses_id'      => $donationRequest->glasses_id,
        'donor_id'        => $donationRequest->donor_id,      // ← هذا السطر المهم
        'recipient_id'    => $donationRequest->recipient_id,
        'status'          => 'pending',
        'recipient_note'  => null,
        'confirmed_at'    => null,
        'denied_at'       => null,
    ]
);

        // ✅ تحديث حالات النظارة والمحادثة
        $lockedGlasses->update([
            'status' => 'pending_donation', // لازم enum يسمح بها
        ]);

        $conversation->update([
            'status' => 'closed',
        ]);

        return [$donationRequest, $confirmation];
    });

    // ✅ إرسال إشعار للمستفيد
    if ($conversation->recipient) {
        $conversation->recipient->notify(
            new RecipientMustConfirmDeliveryNotification($confirmation)
        );
    }

    return redirect()
        ->route('donor.chats.index', ['conversation' => $conversation->id])
        ->with('success', 'Donation request sent. Recipient has been notified to confirm delivery.');
}
}
