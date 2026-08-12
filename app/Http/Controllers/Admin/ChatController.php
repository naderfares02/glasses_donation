<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Glasses;
use App\Models\ContactRequest;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function show(Conversation $conversation)
    {
      
        abort_unless(
            auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'], true),
            403
        );

       
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

        try {
            DB::transaction(function () use ($conversation) {
                $locked = Conversation::whereKey($conversation->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status === 'open') {
                   
                    $locked->loadMissing('request');

                    $glasses = $locked->request
                        ? Glasses::whereKey($locked->request->glasses_id)
                            ->lockForUpdate()
                            ->first()
                        : null;

                    $locked->update(['status' => 'closed']);

                    if ($locked->request) {
                        $locked->request->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                        ]);
                    }

                    if ($glasses && $glasses->status !== 'donated') {
                        $glasses->update([
                            'status' => 'available',
                            'active_contact_request_id' => null,
                        ]);
                    }

                    if ($glasses) {
                        ContactRequest::where('glasses_id', $glasses->id)
                            ->where('status', 'on_hold')
                            ->update(['status' => 'pending']);
                    }
                } else {
                 
                    $locked->loadMissing('request.glasses');

                    if ($locked->request?->glasses?->status === 'donated') {
                        throw new \RuntimeException('GLASSES_ALREADY_DONATED');
                    }

                    $locked->update(['status' => 'open']);
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'GLASSES_ALREADY_DONATED') {
                return back()->with('error', 'Cannot reopen: this donation is already completed.');
            }
            throw $e;
        }

        return back()->with('success', 'Conversation status updated.');
    }
}