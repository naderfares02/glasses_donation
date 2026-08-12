<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintClosedNotification extends Notification
{
    use Queueable;

    public function __construct(public Complaint $complaint) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'         => 'complaint_closed',
            'complaint_id' => $this->complaint->id,
            'title'        => 'Complaint closed',
            'body'         => 'Your complaint has been closed by the admin.',
            'url'          => route('complaints.show', $this->complaint->id),
        ];
    }
}