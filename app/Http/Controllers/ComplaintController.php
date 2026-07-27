<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ComplaintCreatedNotification;
class ComplaintController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        abort_if(!in_array(auth()->user()->role, ['donor','recipient'], true), 403);

        // لازم يكون طرف بالمحادثة
        abort_if(
            auth()->id() !== $conversation->donor_id && auth()->id() !== $conversation->recipient_id,
            403
        );

        $data = $request->validate([
            'reason'      => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $reportedUserId = auth()->id() === $conversation->donor_id
            ? $conversation->recipient_id
            : $conversation->donor_id;

        $complaint = DB::transaction(function () use ($conversation, $data, $reportedUserId) {

            $complaint = Complaint::create([
                'conversation_id'  => $conversation->id,
                'glasses_id'       => $conversation->glasses_id,
                'reporter_id'      => auth()->id(),
                'reported_user_id' => $reportedUserId,
                'reason'           => $data['reason'],
                'description'      => $data['description'] ?? null,
                'status'           => 'open',
            ]);

            // أول رسالة (اختياري): نخزن الوصف كرسالة لو بدك
            if (!empty($data['description'])) {
                ComplaintMessage::create([
                    'complaint_id' => $complaint->id,
                    'sender_id'    => auth()->id(),
                    'sender_role'  => 'user',
                    'body'         => $data['description'],
                ]);
            }

            return $complaint;
        });

        $admins = User::whereIn('role', ['admin','super_admin'])->get();
        Notification::send($admins, new ComplaintCreatedNotification($complaint));

        return redirect()
            ->route('complaints.show', $complaint->id)
            ->with('success', 'Your report has been submitted.');
    }

    public function show(Complaint $complaint)
    {
        abort_if($complaint->reporter_id !== auth()->id(), 403);

        $complaint->load([
            'reportedUser:id,name',
            'conversation.glasses:id,title',
            'messages.sender:id,name',
        ]);

        return view('complaints.show', compact('complaint'));
    }

    public function message(Request $request, Complaint $complaint)
    {
        abort_if($complaint->reporter_id !== auth()->id(), 403);
        abort_if(in_array($complaint->status, ['resolved','dismissed'], true), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $msg = ComplaintMessage::create([
            'complaint_id' => $complaint->id,
            'sender_id'    => auth()->id(),
            'sender_role'  => 'user',
            'body'         => $data['body'],
        ]);

        // لو الأدمن كان بيراجعها، خليها open/أو خليها reviewing حسب منطقك
        if ($complaint->status !== 'open') {
            $complaint->update(['status' => 'open']);
        }


        $targets = collect();

        if ($complaint->handled_by) {
            $handler = User::find($complaint->handled_by);
            if ($handler) $targets->push($handler);
        } else {
            $targets = User::whereIn('role', ['admin', 'super_admin'])->get();
        }

        Notification::send(
            $targets->unique('id'),
            new \App\Notifications\ComplaintMessageFromUserNotification($complaint, $msg)
        );
        return back()->with('success', 'Message sent.');
    }

    public function close(Complaint $complaint)
    {
        abort_if($complaint->reporter_id !== auth()->id(), 403);

        // المستخدم ما يغلقها "dismissed"، فقط "resolved" كطلب إغلاق
        if (!in_array($complaint->status, ['resolved','dismissed'], true)) {
            $complaint->update(['status' => 'resolved']);
        }

        return back()->with('success', 'Complaint closed.');
    }
}