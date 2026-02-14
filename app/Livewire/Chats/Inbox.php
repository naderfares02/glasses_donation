<?php

namespace App\Livewire\Chats;

use Livewire\Component;
use App\Models\Conversation;
use App\Models\Message;

class Inbox extends Component
{
    public ?int $activeConversationId = null;
    public string $body = '';

    protected $queryString = [
        'activeConversationId' => ['as' => 'conversation', 'except' => null],
    ];


    public function mount()
    {
        // إذا URL فيه ?conversation=ID افتحها
        if (!$this->activeConversationId) {
            $first = $this->conversationsQuery()->first();
            $this->activeConversationId = $first?->id;
        }
    }

    private function conversationsQuery()
    {
        $userId = auth()->id();
        $role = auth()->user()->role;

        $q = Conversation::query()
            ->with([
                'glasses.primaryImage',
                'donor:id,name,avatar',
                'recipient:id,name,avatar',
                'messages' => fn ($m) => $m->latest()->limit(1),
            ]);

        if ($role === 'donor') {
            $q->where('donor_id', $userId);
        } elseif ($role === 'recipient') {
            $q->where('recipient_id', $userId);
        } else {
            abort(403);
        }

        return $q->orderByDesc(
            \DB::raw("(SELECT COALESCE(MAX(created_at), '1970-01-01') FROM messages WHERE messages.conversation_id = conversations.id)")
        );
    }

     public function setActive(int $id)
    {
        $this->activeConversationId = $id;

        // علّم رسائل الطرف الآخر كمقروءة
        Message::where('conversation_id', $id)
            ->whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->update(['read_at' => now()]);
    }


    public function send()
    {
        if (!$this->activeConversationId) {
            return;
        }

        $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::findOrFail($this->activeConversationId);

        abort_if(
            auth()->id() !== $conversation->donor_id && auth()->id() !== $conversation->recipient_id,
            403
        );

        if ($conversation->status !== 'open') {
            $this->addError('body', 'Conversation is closed.');
            return;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $this->body,
        ]);

        // realtime event
        event(new \App\Events\MessageSent($message));

        $this->body = '';
    }

    public function getListeners()
    {
        if (!$this->activeConversationId) return [];

        return [
            "echo-private:conversation.{$this->activeConversationId},message.sent" => 'onMessageSent',
        ];
    }

    public function onMessageSent($payload)
    {
        // فقط إعادة render
    }

    public function render()
    {
        $conversations = $this->conversationsQuery()->get();

        $active = $this->activeConversationId
            ? Conversation::with(['glasses.primaryImage', 'donor:id,name', 'recipient:id,name'])
                ->find($this->activeConversationId)
            : null;

        $messages = $this->activeConversationId
            ? Message::with('sender:id,name')
                ->where('conversation_id', $this->activeConversationId)
                ->oldest()
                ->get()
            : collect();

        return view('components.chats.inbox', compact('conversations', 'active', 'messages'));
    }
}
