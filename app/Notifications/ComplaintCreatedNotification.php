<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintCreatedNotification extends Notification
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
            'type' => 'complaint.created',
            'complaint_id' => $this->complaint->id,
            'reporter_name' => $this->complaint->reporter?->name,
            'reported_name' => $this->complaint->reportedUser?->name,
            'reason' => ucfirst(str_replace('_',' ',$this->complaint->reason)),
            'title' => 'New Complaint Submitted',
            'message' =>
                "{$this->complaint->reporter?->name} reported {$this->complaint->reportedUser?->name} for {$this->complaint->reason}.",
            'url' => route('admin.complaints.show', $this->complaint->id),
        ];
    }
}