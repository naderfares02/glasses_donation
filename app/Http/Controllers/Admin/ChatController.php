<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;

class ChatController extends Controller
{
    public function show(Conversation $conversation)
    {
        // السماح فقط للأدمن
         abort_unless(
        auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'], true),
        403
        );

        // تحميل البيانات
        $conversation->load([
            'glasses.primaryImage',
            'messages.sender',
            'donor:id,name,avatar',
            'recipient:id,name,avatar',
        ]);

        $messages = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->oldest()
            ->get();

        return view('admin.chats.show', compact('conversation', 'messages'));
    }

        public function toggleStatus(Conversation $conversation)
        {
            abort_unless(
                auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'], true),
                403
            );

            $newStatus = $conversation->status === 'open' ? 'closed' : 'open';

            $conversation->update([
                'status' => $newStatus,
            ]);

            return back()->with('success', 'Conversation status updated.');
        }
    
}