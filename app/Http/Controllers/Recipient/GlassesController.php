<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Glasses;

class GlassesController extends Controller
{
    public function show(Glasses $glasses)
    {
        $recipientId = auth()->id();

        $isAvailable = $glasses->status === 'available';

        $isPartyToIt = $glasses->contactRequests()->where('recipient_id', $recipientId)->exists()
            || \App\Models\DonationRequest::where('glasses_id', $glasses->id)
                ->where('recipient_id', $recipientId)
                ->exists();

        abort_if(!$isAvailable && !$isPartyToIt, 403);

        $glasses->load(['primaryImage', 'images']);

        return view('recipient.glasses_show', compact('glasses'));
    }
}
