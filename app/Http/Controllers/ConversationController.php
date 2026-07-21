<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{

public function show(Conversation $conversation)
{
    abort_if(
        auth()->id() !== $conversation->donor_id && auth()->id() !== $conversation->recipient_id,
        403
    );

    // 1) علّم رسائل الطرف الآخر كمقروءة
    $conversation->messages()
        ->whereNull('read_at')
        ->where('sender_id', '!=', auth()->id())
        ->update(['read_at' => now()]);

    // 2) علّم إشعارات الرسائل لهذه المحادثة كمقروءة
    // الخيار A (إذا يدعم JSON path عندك)
    auth()->user()->unreadNotifications()
        // ->where('type', NewMessageNotification::class)
        ->where('data->conversation_id', $conversation->id)
        ->update(['read_at' => now()]);

    // إذا واجهت مشكلة في MariaDB، استخدم بدلها:
    /*
    auth()->user()->unreadNotifications()
        ->where('type', NewMessageNotification::class)
        ->where('data', 'like', '%"conversation_id":'.$conversation->id.'%')
        ->update(['read_at' => now()]);
    */

    $conversation->load([
        'glasses.primaryImage',
        'messages.sender',
        'donor',
        'recipient',
    ]);

    $messages = $conversation->messages()->oldest()->get();

    return view('conversations.show', compact('conversation', 'messages'));
}

    public function storeMessage(Request $request, Conversation $conversation)
{
    abort_if(
        auth()->id() !== $conversation->donor_id && auth()->id() !== $conversation->recipient_id,
        403
    );

    if ($conversation->status !== 'open') {
        return back()->with('error', 'Conversation is closed.');
    }

    $data = $request->validate([
        'body' => ['required', 'string', 'max:2000'],
    ]);

    $conversation->loadMissing(['donor', 'recipient']);

    $otherUser = auth()->id() === $conversation->donor_id
        ? $conversation->recipient
        : $conversation->donor;

    DB::transaction(function () use ($conversation, $data, $otherUser) {

      $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        try {
            event(new \App\Events\MessageSent($message));
        } catch (\Throwable $e) {
            // الرسالة محفوظة بالـ DB أصلاً؛ فشل البث اللحظي بس (Reverb واقع مثلاً)
            \Log::warning('Failed to broadcast MessageSent event', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        // if ($otherUser && $otherUser->id !== auth()->id()) {
        //     $otherUser->notify(new NewMessageNotification($conversation, auth()->user()->name));
        // }
    });

    return back();
}
}
