<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonorDonationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public DonationRequest $donationRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $this->donationRequest->loadMissing(['glasses.primaryImage']);

        $g = $this->donationRequest->glasses;

        $reason = $this->donationRequest->admin_note;

        return [
            'type'               => 'donation_request_rejected',
            'donation_request_id'=> $this->donationRequest->id,
            'glasses_id'         => $g?->id,
            'glasses_title'      => $g?->title ?? 'Glasses',
            'message'            => 'Admin rejected your donation request for "' . ($g?->title ?? 'glasses') . '".'
                                    . ($reason ? ' Reason: ' . $reason : ''),
            'url'                => route('donor.chats.index', ['conversation' => $this->donationRequest->conversation_id]),
        ];
    }
}