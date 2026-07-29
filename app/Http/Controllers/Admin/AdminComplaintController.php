<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminComplaintController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin','super_admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $status = $request->query('status', 'all');
        $q = trim((string)$request->query('q', ''));

        $query = Complaint::query()->with([
            'reporter:id,name,email',
            'reportedUser:id,name,email',
        ]);

        if ($status !== 'all' && in_array($status, ['open','reviewing','resolved','dismissed'], true)) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('reason', 'like', "%{$q}%")
                  ->orWhereHas('reporter', fn($u) => $u->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%"))
                  ->orWhereHas('reportedUser', fn($u) => $u->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%"));
            });
        }

        $query->orderByRaw("CASE status
            WHEN 'open' THEN 1
            WHEN 'reviewing' THEN 2
            WHEN 'resolved' THEN 3
            WHEN 'dismissed' THEN 4
            ELSE 5
        END");
        $query->latest();

        $complaints = $query->paginate(15)->withQueryString();

        return view('admin.complaints.index', compact('complaints', 'status', 'q'));
    }

    public function show(Complaint $complaint)
    {
        $this->ensureAdmin();

        $complaint->load([
            'reporter:id,name,email',
            'reportedUser:id,name,email',
            'conversation.glasses:id,title',
            'messages.sender:id,name',
        ]);

        return view('admin.complaints.show', compact('complaint'));
    }

public function reply(Request $request, Complaint $complaint)
{
    $this->ensureAdmin();
    if (
        $complaint->handled_by &&
        $complaint->handled_by !== auth()->id() &&
        auth()->user()->role !== 'super_admin'
    ) {
        return back()->with('error', 'This complaint is already handled by another admin.');
    }
    $data = $request->validate([
        'body' => ['required','string','max:3000'],
    ]);

    $msg = DB::transaction(function () use ($complaint, $data) {

        // ✅ لو في مسؤول بالفعل، امنع أي أدمن ثاني (إلا لو super_admin)
        if ($complaint->handled_by && $complaint->handled_by !== auth()->id()) {
            abort_if(auth()->user()->role !== 'super_admin', 403);
        }

        // ✅ أول أدمن يرد يمسك الشكوى
        if (!$complaint->handled_by) {
            $complaint->update([
                'handled_by' => auth()->id(),
            ]);
        }

        // ✅ أول رد يحولها لـ reviewing (اختياري حسب منطقك)
        if ($complaint->status === 'open') {
            $complaint->update(['status' => 'reviewing']);
        }

        // ✅ أنشئ الرسالة وارجعها (مهم عشان الإشعار)
        return ComplaintMessage::create([
            'complaint_id' => $complaint->id,
            'sender_id'    => auth()->id(),
            'sender_role'  => 'admin',
            'body'         => $data['body'],
        ]);
    });

    // ✅ إشعار للمستخدم صاحب الشكوى
    $complaint->reporter?->notify( new \App\Notifications\ComplaintReplyFromAdminNotification($complaint, $msg));

    return back()->with('success', 'Reply sent.');
}

    public function setStatus(Request $request, Complaint $complaint)
    {
            $this->ensureAdmin();
        if (
            $complaint->handled_by &&
            $complaint->handled_by !== auth()->id() &&
            auth()->user()->role !== 'super_admin'
        ) {
            return back()->with('error', 'This complaint is already handled by another admin.');
        }

        $data = $request->validate([
            'status' => ['required','in:open,reviewing,resolved,dismissed'],
            'resolution_note' => ['nullable','string','max:2000'],
        ]);

        $complaint->update([
            'status' => $data['status'],
            'handled_by' => auth()->id(),
            'resolution_note' => $data['resolution_note'] ?? $complaint->resolution_note,
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function close(Complaint $complaint)
    {
            $this->ensureAdmin();
    if (
        $complaint->handled_by &&
        $complaint->handled_by !== auth()->id() &&
        auth()->user()->role !== 'super_admin'
    ) {
        return back()->with('error', 'This complaint is already handled by another admin.');
    }
        // المستخدم ما يغلقها "dismissed"، فقط "resolved" كطلب إغلاق
        if (!in_array($complaint->status, ['resolved','dismissed'], true)) {
            $complaint->update(['status' => 'resolved']);
        }

        return back()->with('success', 'Complaint closed.');
    }
}