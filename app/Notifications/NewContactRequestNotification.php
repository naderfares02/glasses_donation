<?php

namespace App\Notifications;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContactRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public ContactRequest $request)
    {
        $this->request->loadMissing(['glasses', 'recipient']);
    }

    public function via($notifiable): array
    {
        return ['database']; // فقط داخل الموقع
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'contact_request',
            'contact_request_id' => $this->request->id,
            'glasses_id' => $this->request->glasses_id,
            'glasses_title' => $this->request->glasses?->title,
            'recipient_name' => $this->request->recipient?->name,

            // ✅ الرابط الذي يفتح صفحة الطلبات لهذه النظارة عند المتبرع
            'url' => route('donor.requests.index', $this->request->glasses_id),

            'message' => 'New contact request from ' . ($this->request->recipient?->name ?? 'Someone')
                       . ' about "' . ($this->request->glasses?->title ?? 'glasses') . '"',
        ];
    }
}
