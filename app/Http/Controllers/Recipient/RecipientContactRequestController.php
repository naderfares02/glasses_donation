<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\Glasses;
use App\Models\User;
use App\Notifications\NewContactRequestNotification;
use App\Models\DonationReceipt; 

class RecipientContactRequestController extends Controller
{
   public function index()
{
    $requests = ContactRequest::with(['glasses', 'donor'])
        ->where('recipient_id', auth()->id())
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

    $donatedGlassesIds = DonationReceipt::where('recipient_id', auth()->id())
        ->pluck('glasses_id')
        ->toArray();

    return view('recipient.glasses_requests', compact('requests', 'donatedGlassesIds'));
} 
  public function store(Glasses $glasses)
{
    if ($glasses->status !== 'available') {
        return back()->with('error', 'This glasses is not available.');
    }

    $recipientId = auth()->id();

    $exists = ContactRequest::where('glasses_id', $glasses->id)
        ->where('recipient_id', $recipientId)
        ->whereIn('status', ['pending', 'accepted', 'on_hold', 'rejected'])
        ->exists();

    if ($exists) {
        return back()->with(
            'error',
            'You cannot send another contact request for this glasses.'
        );
    }

    try {
        $request = ContactRequest::create([
            'glasses_id'   => $glasses->id,
            'donor_id'     => $glasses->user_id,
            'recipient_id' => $recipientId,
            'status'       => 'pending',
        ]);
    } catch (\Illuminate\Database\QueryException $e) {
        return back()->with('error', 'You already have an active request for this glasses.');
    }

    $donor = User::find($glasses->user_id);

    if ($donor) {
        $donor->notify(new NewContactRequestNotification($request));
    }

    return back()->with('success', 'Contact request sent.');
}

public function withdraw(ContactRequest $request)
{
    abort_if($request->recipient_id !== auth()->id(), 403);

    if ($request->status !== 'pending') {
        return back()->with('error', 'You can only withdraw pending requests.');
    }

    $request->delete();

    return back()->with('success', 'Request withdrawn successfully.');
}

}
