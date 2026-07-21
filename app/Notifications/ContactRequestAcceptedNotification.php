<?php

namespace App\Notifications;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContactRequestAcceptedNotification extends Notification
{
    use Queueable;

    protected ContactRequest $request;

    public function __construct(ContactRequest $request)
    {
        $this->request = $request;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Contact Request Accepted',
            'message' => 'your contact request has been accepted by the donor.',
            'glasses_id' => $this->request->glasses_id,
            'contact_request_id' => $this->request->id,
            'url' => route('recipient.contact-requests.index'),
        ];
    }
}