<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use App\Models\Conversation;
use App\Models\DonationRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $glassesQuery = Glasses::where('user_id', $userId);

        $stats = [
            'glasses_total'   => (clone $glassesQuery)->count(),
            'available'       => (clone $glassesQuery)->where('status', 'available')->count(),
            'reserved'        => (clone $glassesQuery)->where('status', 'reserved')->count(),
            'in_contact'      => (clone $glassesQuery)->where('status', 'in_contact')->count(),
            'pending_donation'=> (clone $glassesQuery)->where('status', 'pending_donation')->count(),
            'donated'         => (clone $glassesQuery)->where('status', 'donated')->count(),

            'conversations_open' => Conversation::where('donor_id', $userId)->where('status', 'open')->count(),

            'donation_requests_pending'  => DonationRequest::where('donor_id', $userId)->where('status', 'pending')->count(),
            'donation_requests_approved' => DonationRequest::where('donor_id', $userId)->where('status', 'approved')->count(),
            'donation_requests_rejected' => DonationRequest::where('donor_id', $userId)->where('status', 'rejected')->count(),
        ];

        return view('donor.main_page', compact('stats'));
    }
}