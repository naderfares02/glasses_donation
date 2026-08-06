<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminRecipientDeniedDeliveryNotification extends Notification
{
    use Queueable;

    public function __construct(public DonationRequest $donationRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $this->donationRequest->loadMissing(['glasses', 'deliveryConfirmation']);

        $g = $this->donationRequest->glasses;
        $note = $this->donationRequest->deliveryConfirmation?->recipient_note;

        return [
            'type'                => 'donation_recipient_denied_delivery',
            'donation_request_id' => $this->donationRequest->id,
            'glasses_id'          => $g?->id,
            'glasses_title'       => $g?->title ?? 'Glasses',
            'message'             => 'Recipient reported NOT receiving the glasses for "'
                                        . ($g?->title ?? 'glasses') . '". Needs review.'
                                        . ($note ? ' Note: ' . $note : ''),
            'url'                 => route('admin.donation_requests.show', $this->donationRequest->id),
        ];
    }
}