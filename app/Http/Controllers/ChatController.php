<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
class ChatController extends Controller
{
    public function index(Request $request)
{
    $userId = auth()->id();
    $role = auth()->user()->role;

    $query = Conversation::query()
        ->with([
            'glasses.primaryImage',
            'donor:id,name',
            'recipient:id,name',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ]);

    if ($role === 'donor') {
        $query->where('donor_id', $userId);
    } elseif ($role === 'recipient') {
        $query->where('recipient_id', $userId);
    } else {
        abort(403);
    }

    $conversations = $query
        ->orderByDesc(
            \DB::raw("(SELECT COALESCE(MAX(created_at), '1970-01-01') 
            FROM messages 
            WHERE messages.conversation_id = conversations.id)")
        )
        ->get();

    // المحادثة المطلوبة من الرابط
    $activeConversation = null;

    if ($request->filled('conversation')) {
        $activeConversation = $conversations
            ->where('id', $request->conversation)
            ->first();
    }

    // إذا لم يتم تحديد محادثة
    if (!$activeConversation) {
        $activeConversation = $conversations->first();
    }

    return view('chats.index', compact('conversations', 'activeConversation'));
}
}
