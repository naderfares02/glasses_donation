<?php

namespace App\Notifications;

use App\Models\DeliveryConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DeliveryConfirmationReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public DeliveryConfirmation $confirmation) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->confirmation->loadMissing(['donationRequest.glasses']);

        $glasses = $this->confirmation->donationRequest?->glasses;
        $title = $glasses?->title ?? 'the glasses';

        $url = route('recipient.confirmations.show', $this->confirmation->id);

        return (new MailMessage)
            ->subject('Reminder: please confirm delivery of "' . $title . '"')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('It has been 3 days since you were asked to confirm whether you received "' . $title . '".')
            ->line('Please let us know if you received it so we can close this donation.')
            ->action('Confirm delivery', $url)
            ->line('If you already received it or have an issue, please respond as soon as possible.');
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
            'message' => 'Reminder: please confirm if you received "' . ($g?->title ?? 'these glasses') . '".',
            'url' => route('recipient.confirmations.show', $this->confirmation->id),
        ];
    }
}