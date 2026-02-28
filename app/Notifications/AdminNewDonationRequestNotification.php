<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminNewDonationRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public DonationRequest $donationRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $this->donationRequest->loadMissing(['glasses.primaryImage', 'donor:id,name', 'recipient:id,name']);

        $g = $this->donationRequest->glasses;

        return [
            'type'               => 'new_donation_request',
            'donation_request_id'=> $this->donationRequest->id,
            'glasses_id'         => $g?->id,
            'glasses_title'      => $g?->title ?? 'Glasses',
            'message'            => 'New donation request submitted by donor. Please review it.',
            // لو بدك تعرضها لاحقاً (اختياري)
            'donor_name'         => $this->donationRequest->donor?->name,
            'recipient_name'     => $this->donationRequest->recipient?->name,

            // رابط صفحة مراجعة الطلب عند الأدمن
            'url'                => route('admin.donation_requests.show', $this->donationRequest->id),
        ];
    }
}