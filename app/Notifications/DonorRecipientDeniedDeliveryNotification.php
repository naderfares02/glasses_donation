<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonorRecipientDeniedDeliveryNotification extends Notification
{
    use Queueable;

    public function __construct(public DonationRequest $donationRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $this->donationRequest->loadMissing(['glasses.primaryImage', 'deliveryConfirmation']);

        $g = $this->donationRequest->glasses;
        $note = $this->donationRequest->deliveryConfirmation?->recipient_note;

        return [
            'type'                => 'donation_recipient_denied_delivery',
            'donation_request_id' => $this->donationRequest->id,
            'glasses_id'          => $g?->id,
            'glasses_title'       => $g?->title ?? 'Glasses',
            'message'             => 'The recipient said they did NOT receive "'
                                        . ($g?->title ?? 'the glasses') . '". An admin will review this.'
                                        . ($note ? ' Their note: ' . $note : ''),
            'url'                 => route('donor.chats.index', ['conversation' => $this->donationRequest->conversation_id]),
        ];
    }
}