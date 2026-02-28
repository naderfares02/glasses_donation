<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\Glasses;
use App\Models\User;
use App\Notifications\NewContactRequestNotification;

class RecipientContactRequestController extends Controller
{
   public function store(Glasses $glasses)
{
    if ($glasses->status !== 'available') {
        return back()->with('error', 'This glasses is not available.');
    }

    $recipientId = auth()->id();

    // هل يوجد طلب سابق لنفس المستفيد على نفس النظارة ولم يُغلق؟
    $exists = ContactRequest::where('glasses_id', $glasses->id)
        ->where('recipient_id', $recipientId)
        ->whereIn('status', ['pending', 'accepted'])
        ->exists();

    if ($exists) {
        return back()->with('error', 'You already requested contact for this glasses.');
    }

    $request = ContactRequest::create([
        'glasses_id'   => $glasses->id,
        'donor_id'     => $glasses->user_id,
        'recipient_id' => $recipientId,
        'status'       => 'pending',
    ]);

    // ✅ إرسال إشعار للمتبرع
    $donor = User::find($glasses->user_id);
    if ($donor) {
        $donor->notify(new NewContactRequestNotification($request));
    }

    return back()->with('success', 'Contact request sent.');
}

}
