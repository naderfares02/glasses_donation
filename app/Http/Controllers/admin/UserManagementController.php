<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ContactRequest;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
public function index(Request $request)
{
    $q      = $request->query('q');
    $role   = $request->query('role', 'all');
    $status = $request->query('status', 'all');

    $query = User::query()->withTrashed();

    // Search
    if ($search = trim((string)$q)) {
        $query->where(function ($x) use ($search) {
            $x->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Role filter
    if ($role !== 'all') {
        $query->where('role', $role);
    }

    // Status filter
    if ($status !== 'all') {
        $query->where('status', $status);
    }

    // ✅ ترتيب حسب الدور: super_admin ثم admin ثم donor ثم recipient
    $query->orderByRaw("FIELD(role, 'super_admin','admin','donor','recipient')");

    // ترتيب داخل نفس الدور (اختياري)
    $query->latest(); // أو ->orderBy('name')

    $users = $query->paginate(12)->withQueryString();

    $counts = User::withTrashed()
        ->selectRaw("
            SUM(role='donor') as donors,
            SUM(role='recipient') as recipients,
            SUM(role='admin') as admins,
            SUM(role='super_admin') as super_admins,
            SUM(status='suspended') as suspended,
            SUM(deleted_at IS NOT NULL) as deleted_count
        ")
        ->first();

    return view('admin.users.index', compact('users', 'counts', 'q', 'role', 'status'));
}
    public function show(User $user)
    {
        $user->loadMissing(['suspendedBy:id,name', 'roleChangedBy:id,name']);

        // إحصائيات بسيطة (وسعها لاحقًا)
        $stats = [
            'conversations' => Conversation::where('donor_id', $user->id)->orWhere('recipient_id', $user->id)->count(),
            'contact_requests' => ContactRequest::where('donor_id', $user->id)->orWhere('recipient_id', $user->id)->count(),
            'donation_requests' => DonationRequest::where('donor_id', $user->id)->orWhere('recipient_id', $user->id)->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // تعديل بيانات بسيطة (بدون role هنا)
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
        ]);

        $user->update($data);

        return back()->with('success', 'User updated.');
    }

    public function suspend(Request $request, User $user)
    {
        // ممنوع الأدمن يوقف نفسه
        abort_if($user->id === auth()->id(), 403);

        $data = $request->validate([
            'reason' => ['required','string','max:255'],
        ]);

        // super_admin فقط يستطيع توقيف admin/super_admin
        if (in_array($user->role, ['admin','super_admin']) && auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_by' => auth()->id(),
            'suspended_reason' => $data['reason'],
        ]);

        return back()->with('success', 'User suspended.');
    }

    public function unsuspend(User $user)
    {
        if (in_array($user->role, ['admin','super_admin']) && auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $user->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_by' => null,
            'suspended_reason' => null,
        ]);

        return back()->with('success', 'User unsuspended.');
    }

    public function changeRole(Request $request, User $user)
    {
        // فقط super_admin
        abort_if(auth()->user()->role !== 'super_admin', 403);

        // ممنوع تغيّر دور نفسك
        abort_if($user->id === auth()->id(), 403);

        $data = $request->validate([
            'role' => ['required','in:donor,recipient,admin,super_admin'],
        ]);

        $user->update([
            'role' => $data['role'],
            'role_changed_by' => auth()->id(),
            'role_changed_at' => now(),
        ]);

        return back()->with('success', 'Role updated.');
    }

    public function destroy(User $user)
    {
        // فقط super_admin يحذف
        abort_if(auth()->user()->role !== 'super_admin', 403);
        abort_if($user->id === auth()->id(), 403);

        $user->delete();

        return back()->with('success', 'User deleted (soft).');
    }

    public function restore($id)
    {
        abort_if(auth()->user()->role !== 'super_admin', 403);

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User restored.');
    }

    public function closeOpenConversations(User $user)
    {
        // super_admin فقط (لأنه إجراء قوي)
        abort_if(auth()->user()->role !== 'super_admin', 403);

        DB::transaction(function () use ($user) {
            Conversation::where('status', 'open')
                ->where(function ($q) use ($user) {
                    $q->where('donor_id', $user->id)->orWhere('recipient_id', $user->id);
                })
                ->update(['status' => 'closed']);
        });

        return back()->with('success', 'All open conversations closed.');
    }
}