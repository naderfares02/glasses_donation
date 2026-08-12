<?php

namespace App\Notifications;

use App\Models\DeliveryConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RecipientMustConfirmDeliveryNotification extends Notification
{
    use Queueable;

    public function __construct(public DeliveryConfirmation $confirmation) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

        public function toDatabase($notifiable): array
    {
        $this->confirmation->loadMissing(['donationRequest.glasses.primaryImage']);

        $dr = $this->confirmation->donationRequest;
        $g  = $dr?->glasses;

        return [
            'confirmation_id' => $this->confirmation->id,
            'glasses_id'      => $g?->id,
            'glasses_title'   => $g?->title ?? 'Glasses',

            'message' => 'Please confirm if you received "' . ($g?->title ?? 'these glasses') . '".',

            'url' => route('recipient.confirmations.show', $this->confirmation->id),
        ];
    }

}