<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminRecipientConfirmedDeliveryNotification extends Notification
{
    use Queueable;

    public function __construct(public DonationRequest $donationRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $g = $this->donationRequest->glasses;

        return [
            'donation_request_id' => $this->donationRequest->id,
            'glasses_id'          => $g?->id,
            'glasses_title'       => $g?->title ?? 'Glasses',
            'message'             => 'Recipient confirmed receiving the glasses. Please review the donation request.',
            // ✅ عدّل اسم الراوت إذا عندك مختلف
            'url'                 => route('admin.donation_requests.show', $this->donationRequest->id),
        ];
    }
}