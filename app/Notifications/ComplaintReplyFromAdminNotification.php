<?php

namespace App\Notifications;

use App\Models\Complaint;
use App\Models\ComplaintMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintReplyFromAdminNotification extends Notification 
{
    use Queueable;

    public function __construct(
        public Complaint $complaint,
        public ComplaintMessage $message
    ) {}

    public function via($notifiable): array
    {
        return ['database']; // حالياً
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'         => 'complaint.reply_from_admin',
            'complaint_id' => $this->complaint->id,
            'title'        => 'Support replied to your report',
            'body'         => mb_strimwidth($this->message->body, 0, 120, '...'),
            'url'          => route('complaints.show', $this->complaint->id),
        ];
    }
}