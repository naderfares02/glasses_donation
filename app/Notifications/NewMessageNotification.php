<?php

// namespace App\Notifications;

// use App\Models\Conversation;
// use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Notification;

// class NewMessageNotification extends Notification
// {
//     use Queueable;

//     public function __construct(public Conversation $conversation, public string $senderName)
//     {
//         $this->conversation->loadMissing('glasses');
//     }

//     public function via($notifiable): array
//     {
//         return ['database'];
//     }

//     public function toDatabase($notifiable): array
//     {
//         // رابط صحيح حسب دور المستلم
//         $url = $notifiable->role === 'donor'
//             ? route('donor.conversations.show', $this->conversation->id)
//             : route('recipient.conversations.show', $this->conversation->id);

//         return [
//             'type' => 'message_received',
//             'title' => 'New message',
//             'message' => $this->senderName . ' sent you a message about "' . $this->conversation->glasses->title . '"',
//             'conversation_id' => $this->conversation->id,
//             'url' => $url,
//         ];
//     }
// }
