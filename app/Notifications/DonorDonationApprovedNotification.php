<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonorDonationApprovedNotification extends Notification
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

        return [
            'type'               => 'donation_request_approved',
            'donation_request_id'=> $this->donationRequest->id,
            'glasses_id'         => $g?->id,
            'glasses_title'      => $g?->title ?? 'Glasses',
            'message'            => 'Admin approved your donation request for "' . ($g?->title ?? 'glasses') . '".',
            // لو عندك صفحة تفاصيل الطلب عند الأدمن/المتبرع غيّر الراوت
            'url'                => route('donor.chats.index', ['conversation' => $this->donationRequest->conversation_id]),
        ];
    }
}