<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Glasses;
use App\Models\Conversation;
use App\Models\ContactRequest;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
public function index(Request $request)
{
    $q       = $request->query('q', '');
    $role    = $request->query('role', 'all');
    $status  = $request->query('status', 'all');
    $deleted = $request->query('deleted', 'all'); 

    $query = User::query()
        ->withTrashed()
        ->where('id', '!=', auth()->id())        
        ->where('role', '!=', 'super_admin');     

    if (auth()->user()->role === 'admin') {
        $query->whereNotIn('role', ['admin', 'super_admin']);
    }
    
    // Search
    if ($search = trim((string) $q)) {
        $query->where(function ($x) use ($search) {
            $x->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }


    if ($role !== 'all' && in_array($role, ['admin', 'donor', 'recipient'], true)) {
        $query->where('role', $role);
    }

    if ($status !== 'all' && in_array($status, ['active', 'suspended'], true)) {
        $query->where('status', $status);
    }


    if ($deleted === '1') {
        $query->onlyTrashed();
    } elseif ($deleted === '0') {
        $query->whereNull('deleted_at');
    }

    $query->orderByRaw("
        CASE role
            WHEN 'admin' THEN 1
            WHEN 'donor' THEN 2
            WHEN 'recipient' THEN 3
            ELSE 4
        END
    ");
    $query->orderByDesc('created_at');

    $users = $query->paginate(12)->withQueryString();


    $countsRow = User::withTrashed()
        ->selectRaw("
            SUM(role='donor') as donors,
            SUM(role='recipient') as recipients,
            SUM(role='admin') as admins,
            SUM(role='super_admin') as super_admins,
            SUM(status='suspended') as suspended,
            SUM(deleted_at IS NOT NULL) as deleted_count,
            COUNT(*) as all_count
        ")
        ->first();

    $counts = [
        'all'         => (int) ($countsRow->all_count ?? 0),
        'donor'       => (int) ($countsRow->donors ?? 0),
        'recipient'   => (int) ($countsRow->recipients ?? 0),
        'admin'       => (int) ($countsRow->admins ?? 0),
        'super_admin' => (int) ($countsRow->super_admins ?? 0),
        'suspended'   => (int) ($countsRow->suspended ?? 0),
        'deleted'     => (int) ($countsRow->deleted_count ?? 0),
    ];

    return view('admin.users.index', compact('users', 'counts', 'q', 'role', 'status', 'deleted'));
}

public function show(User $user)
{
    $user->loadMissing(['suspendedBy:id,name', 'roleChangedBy:id,name']);


    $convQuery = Conversation::query()
        ->where(function ($q) use ($user) {
            $q->where('donor_id', $user->id)
              ->orWhere('recipient_id', $user->id);
        });

$stats = [];

if ($user->role === 'donor') {

    $stats = [
        'conversations' => (clone $convQuery)->count(),

        'contact_requests' => ContactRequest::where('donor_id', $user->id)->count(),

        'donations_pending' => DonationRequest::where('donor_id', $user->id)
            ->where('status', 'pending')
            ->count(),

        'donations_rejected' => DonationRequest::where('donor_id', $user->id)
            ->where('status', 'rejected')
            ->count(),

        'donations_approved' => DonationRequest::where('donor_id', $user->id)
            ->where('status', 'approved')
            ->count(),

        'glasses_posted' => Glasses::where('user_id', $user->id)->count(),
    ];
}

elseif ($user->role === 'recipient') {
    $stats = [
        'conversations'     => (clone $convQuery)->count(),
        'contact_requests'  => ContactRequest::where('recipient_id', $user->id)->count(), // أرسلها
        'donation_requests' => DonationRequest::where('recipient_id', $user->id)->count(),
    ];
}
else {
    $stats = [
        'conversations' => (clone $convQuery)->count(),
    ];
}

    return view('admin.users.show', compact(
        'user',
        'stats',
    ));
}


public function openClosedConversations(User $user)
{
    abort_if(auth()->user()->role !== 'super_admin', 403);

    DB::transaction(function () use ($user) {
        Conversation::where('status', 'closed')
            ->where(function ($q) use ($user) {
                $q->where('donor_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->update(['status' => 'open']);
    });

    return back()->with('success', 'All closed conversations opened.');
}


    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

        public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                \Illuminate\Validation\Rule::unique(User::class)->ignore($user->id),
            ],
            'phone' => ['required','string','max:30'],
            'city'  => ['required','string','max:255'],
        ]);

        $user->update($data);

        return back()->with('success', 'User updated.');
    }

    public function suspend(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 403);

        $data = $request->validate([
            'reason' => ['required','string','max:255'],
        ]);

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
        abort_if(auth()->user()->role !== 'super_admin', 403);
        abort_if($user->id === auth()->id(), 403);

        $data = $request->validate([
            'role' => ['required','in:donor,recipient,admin,super_admin'],
        ]);

        $hasActiveGlasses = Glasses::where('user_id', $user->id)
            ->whereNotIn('status', ['available', 'donated'])
            ->exists();

        $hasOpenConversations = Conversation::where('status', 'open')
            ->where(function ($q) use ($user) {
                $q->where('donor_id', $user->id)->orWhere('recipient_id', $user->id);
            })->exists();

        $hasPendingConfirmations = \App\Models\DeliveryConfirmation::where('recipient_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        $hasPendingDonationRequests = DonationRequest::where('donor_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasActiveGlasses || $hasOpenConversations || $hasPendingConfirmations || $hasPendingDonationRequests) {
            return back()->with('error', 'Cannot change role: this user has active conversations, pending donations, or unresolved confirmations. Resolve them first.');
        }

        $user->update([
            'role' => $data['role'],
            'role_changed_by' => auth()->id(),
            'role_changed_at' => now(),
        ]);

        return back()->with('success', 'Role updated.');
    }

    public function destroy(User $user)
    {
        abort_if(auth()->user()->role !== 'super_admin', 403);
        abort_if($user->id === auth()->id(), 403);

        DB::transaction(function () use ($user) {
            Glasses::where('user_id', $user->id)
                ->whereIn('status', ['available', 'reserved', 'in_contact'])
                ->update(['status' => 'donated']);

            $user->delete();
        });

        return back()->with('success', 'User deleted (soft). Their active listings were withdrawn.');
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