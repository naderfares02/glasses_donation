<?php

namespace App\Notifications;

use App\Models\Complaint;
use App\Models\ComplaintMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ComplaintMessageFromUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Complaint $complaint,
        public ComplaintMessage $message
    ) {}

    public function via($notifiable): array
    {
        // يمكنك إضافة 'database' لو بدك إشعار داخل الموقع
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $userName = $this->complaint->user?->name ?? 'User';
        $subject = "New complaint message from {$userName} (Complaint #{$this->complaint->id})";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("You received a new message on a complaint.")
            ->line("Complaint ID: #{$this->complaint->id}")
            ->line("From: {$userName}")
            ->line("Message:")
            ->line($this->message->body)
            ->action('Open Complaint', $this->complaintUrl())
            ->line('Please review and reply if needed.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'complaint.message.from_user',
            'complaint_id' => $this->complaint->id,
            'message_id' => $this->message->id,
            'title' => 'New message from user',
            'body' => str($this->message->body)->limit(140),
            'url' => $this->complaintUrl(),
        ];
    }

    private function complaintUrl(): string
    {
        // عدّل الراوت حسب مشروعك
        return route('admin.complaints.show', $this->complaint->id);
    }
}