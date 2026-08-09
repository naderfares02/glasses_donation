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
use App\Notifications\ContactRequestRejectedNotification;
use App\Notifications\ContactRequestAcceptedNotification;
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

    $request->recipient->notify(
        new ContactRequestAcceptedNotification($request)
    );

    return back()->with('success', 'Request accepted.');
}


public function disconnect(Conversation $conversation)
{
    abort_if($conversation->donor_id !== auth()->id(), 403);

    // ✅ فحص أولي سريع (تجربة مستخدم أفضل، بدون الاعتماد عليه للحماية الفعلية)
    if ($conversation->status !== 'open') {
        return redirect()
            ->route('donor.chats.index', ['conversation' => $conversation->id])
            ->with('error', 'This conversation is already closed.');
    }

    try {
        DB::transaction(function () use ($conversation) {

            // ✅ الحماية الفعلية: قفل صف الـ conversation وإعادة التحقق من حالتها
            // داخل نفس الـ transaction، لمنع تنفيذ disconnect() مرتين بالتوازي
            // (أو بالتوازي مع markDonated() على نفس المحادثة).
            $lockedConversation = Conversation::whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedConversation->status !== 'open') {
                throw new \RuntimeException('CONVERSATION_NOT_OPEN');
            }

            $lockedConversation->loadMissing(['request']);

            $glasses = $lockedConversation->request
                ? Glasses::whereKey($lockedConversation->request->glasses_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            $lockedConversation->update(['status' => 'closed']);

            if ($lockedConversation->request) {
                $lockedConversation->request->update([
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

            if ($glasses) {
                ContactRequest::where('glasses_id', $glasses->id)
                    ->where('status', 'on_hold')
                    ->update(['status' => 'pending']);
            }
        });
    } catch (\RuntimeException $e) {
        if ($e->getMessage() === 'CONVERSATION_NOT_OPEN') {
            return redirect()
                ->route('donor.chats.index', ['conversation' => $conversation->id])
                ->with('error', 'This conversation is already closed.');
        }
        throw $e;
    }

    return redirect()
        ->route('donor.chats.index', ['conversation' => $conversation->id])
        ->with('success', 'Connection closed. Glasses is available again.');
}


public function reject(ContactRequest $request)
{
    abort_if($request->donor_id !== auth()->id(), 403);

    // ✅ منع رفض طلب مش pending (مقبول/مرفوض/مغلق أصلاً)
    if ($request->status !== 'pending') {
        return back()->with('error', 'This request is not pending.');
    }

    $request->update(['status' => 'rejected']);

    $request->recipient->notify(
        new ContactRequestRejectedNotification($request)
    );

    return back()->with('success','Request rejected.');
}


public function markDonated(Request $request, Glasses $glasses)
{
    abort_if($glasses->user_id !== auth()->id(), 403);

    $data = $request->validate([
        'conversation_id' => ['required','integer'],
        'delivered_date'  => ['date','required'],
        'donor_note'      => ['nullable', 'string', 'max:2000'],
    ]);

    // ✅ فحص أولي سريع (تجربة مستخدم أفضل، بدون الاعتماد عليه للحماية الفعلية)
    $conversationExists = Conversation::where('id', $data['conversation_id'])
        ->where('glasses_id', $glasses->id)
        ->where('donor_id', auth()->id())
        ->exists();

    if (!$conversationExists) {
        return back()->with('error', 'No conversation found for this glasses.');
    }

    $requireAdminApproval = (bool) setting('donations.require_admin_approval_for_donated', true);

    try {
        [$donationRequest, $confirmation, $conversation] = DB::transaction(function () use ($glasses, $data, $requireAdminApproval) {

            $lockedGlasses = Glasses::whereKey($glasses->id)
                ->lockForUpdate()
                ->firstOrFail();

            // ✅ الحماية الفعلية: قفل صف الـ conversation والتحقق من حالتها
            // داخل نفس الـ transaction، بعد ما صار عندنا lock على Glasses.
            // هيك ما ينفذ طلبين "markDonated" لنفس المحادثة بنفس اللحظة.
            $conversation = Conversation::where('id', $data['conversation_id'])
                ->where('glasses_id', $lockedGlasses->id)
                ->where('donor_id', auth()->id())
                ->lockForUpdate()
                ->first();

            if (!$conversation || $conversation->status !== 'open') {
                throw new \RuntimeException('CONVERSATION_NOT_OPEN');
            }

            $conversation->loadMissing('recipient');

            $donationStatus = $requireAdminApproval ? 'pending' : 'approved';

            $existingPending = DonationRequest::where('glasses_id', $lockedGlasses->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            $payload = [
                'conversation_id' => $conversation->id,
                'donor_id'        => $conversation->donor_id,
                'recipient_id'    => $conversation->recipient_id,
                'delivered_date'  => $data['delivered_date'] ?? null,
                'donor_note'      => $data['donor_note'] ?? null,
                'status'          => $donationStatus,
            ];

            if ($existingPending) {
                $existingPending->update($payload);
                $donationRequest = $existingPending;
            } else {
                $donationRequest = DonationRequest::create(array_merge(
                    ['glasses_id' => $lockedGlasses->id],
                    $payload
                ));
            }

            $confirmation = DeliveryConfirmation::updateOrCreate(
                ['donation_request_id' => $donationRequest->id],
                [
                    'conversation_id' => $donationRequest->conversation_id,
                    'glasses_id'      => $donationRequest->glasses_id,
                    'donor_id'        => $donationRequest->donor_id,
                    'recipient_id'    => $donationRequest->recipient_id,
                    'status'          => 'pending',
                    'recipient_note'  => null,
                    'confirmed_at'    => null,
                    'denied_at'       => null,
                ]
            );

            $lockedGlasses->update([
                'status' => $requireAdminApproval ? 'pending_donation' : 'donated',
            ]);

            $conversation->update(['status' => 'closed']);

            return [$donationRequest, $confirmation, $conversation];
        });
} catch (\RuntimeException $e) {
        if ($e->getMessage() === 'CONVERSATION_NOT_OPEN') {
            return back()->with('error', 'No open conversation found for this glasses.');
        }
        throw $e;
    }

   $confirmation->recipient?->notify(new RecipientMustConfirmDeliveryNotification($confirmation));

    // الأدمن لازم يتنبّه بس لو الطلب فعليًا بحاجة مراجعته (pending)،
    // مش لو انعمد تلقائيًا (approved) بدون تدخّله أصلًا
    if ($requireAdminApproval) {
        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\AdminNewDonationRequestNotification($donationRequest));
    }

    return redirect()->route('donor.chats.index', ['conversation' => $conversation->id])->with(
        'success',
        $requireAdminApproval
            ? 'Marked as delivered. Waiting for admin approval.'
            : 'Marked as delivered successfully.'
    );
}
}