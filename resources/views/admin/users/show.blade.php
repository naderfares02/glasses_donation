<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">User Details</h2>
                <p class="text-sm text-gray-500 mt-1">User #{{ $user->id }}</p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to users
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Top card --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-start gap-6">

                    {{-- Avatar --}}
                    <div class="shrink-0">
                        <div
                            class="w-24 h-24 rounded-2xl overflow-hidden border bg-gray-100 flex items-center justify-center">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover"
                                    alt="avatar">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center text-3xl font-extrabold text-gray-700">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        @if($user->deleted_at)
                            <span
                                class="mt-3 inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border
                                                                                                                bg-gray-100 text-gray-700 border-gray-200">
                                Deleted
                            </span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-xl font-bold text-gray-900 truncate">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1 truncate">{{ $user->email }}</p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    {{-- Role badge --}}
                                    @php
                                        $roleBadge = match ($user->role) {
                                            'donor' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'recipient' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'admin' => 'bg-gray-100 text-gray-800 border-gray-200',
                                            'super_admin' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };

                                        $statusBadge = ($user->status === 'active')
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : 'bg-red-50 text-red-700 border-red-200';
                                    @endphp

                                    <span
                                        class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border {{ $roleBadge }}">
                                        {{ strtoupper($user->role) }}
                                    </span>

                                    <span
                                        class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border {{ $statusBadge }}">
                                        {{ strtoupper($user->status) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Quick actions --}}
                            <div class="flex flex-wrap gap-2">
                                {{-- <a href="{{ route('admin.users.conversations', $user->id) }}"
                                    class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                    View Conversations
                                </a> --}}

                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                    Edit
                                </a>
                            </div>
                        </div>

                        {{-- Details grid --}}
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $user->phone ?? '—' }}
                                </p>
                            </div>

                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">City</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $user->city ?? '—' }}
                                </p>
                            </div>

                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Joined</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $user->created_at?->format('Y-m-d H:i') ?? '—' }}
                                </p>
                            </div>

                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Last update</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $user->updated_at?->format('Y-m-d H:i') ?? '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Suspended details --}}
                        @if($user->status === 'suspended')
                            <div class="mt-5 p-4 rounded-xl border bg-red-50 border-red-200">
                                <p class="text-sm font-bold text-red-800">Suspended</p>
                                <p class="text-sm text-red-700 mt-1">
                                    <span class="font-semibold">Reason:</span>
                                    {{ $user->suspended_reason ?? '—' }}
                                </p>
                                <p class="text-sm text-red-700 mt-1">
                                    <span class="font-semibold">By:</span>
                                    {{ $user->suspendedBy?->name ?? '—' }}
                                </p>
                                <p class="text-sm text-red-700 mt-1">
                                    <span class="font-semibold">At:</span>
                                    {{ $user->suspended_at ? $user->suspended_at->format('Y-m-d H:i') : '—' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            @if(in_array($user->role, ['donor', 'recipient']))
                @php
                    $cols = $user->role === 'donor' ? 'lg:grid-cols-4' : 'lg:grid-cols-3';
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 {{ $cols }} gap-4 items-stretch">

                    {{-- Conversations --}}
                    <div class="bg-white border rounded-2xl p-5 h-full flex flex-col">
                        <p class="text-xs text-gray-500">Conversations</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-2">{{ $stats['conversations'] ?? 0 }}</p>
                        <div class="mt-auto text-xs text-gray-400">Total chats</div>
                    </div>

                    {{-- Contact Requests --}}
                    <div class="bg-white border rounded-2xl p-5 h-full flex flex-col">
                        <p class="text-xs text-gray-500">Contact Requests</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-2">{{ $stats['contact_requests'] ?? 0 }}</p>
                        <div class="mt-auto text-xs text-gray-400">All requests</div>
                    </div>

                    {{-- Donation Requests (compact) --}}
                    <div class="bg-white border rounded-2xl p-5">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs text-gray-500">Donation Requests</p>

                            <span class="text-[11px] font-semibold text-gray-600">
                                Total:
                                {{ ($stats['donations_pending'] ?? 0) + ($stats['donations_rejected'] ?? 0) + ($stats['donations_approved'] ?? 0) }}
                            </span>
                        </div>

                        {{-- mini boxes --}}
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            {{-- Pending --}}
                            <div class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2">
                                <p class="text-[10px] font-bold text-blue-700 leading-none">PENDING</p>
                                <p class="text-xl font-extrabold text-blue-900 mt-1 leading-none">
                                    {{ $stats['donations_pending'] ?? 0 }}
                                </p>
                            </div>

                            {{-- Rejected --}}
                            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2">
                                <p class="text-[10px] font-bold text-red-700 leading-none">REJECTED</p>
                                <p class="text-xl font-extrabold text-red-900 mt-1 leading-none">
                                    {{ $stats['donations_rejected'] ?? 0 }}
                                </p>
                            </div>

                            {{-- Approved --}}
                            <div class="rounded-xl border border-green-200 bg-green-50 px-3 py-2">
                                <p class="text-[10px] font-bold text-green-700 leading-none">APPROVED</p>
                                <p class="text-xl font-extrabold text-green-900 mt-1 leading-none">
                                    {{ $stats['donations_approved'] ?? 0 }}
                                </p>
                            </div>
                        </div>

                        {{-- small hint --}}
                        <p class="text-[11px] text-gray-400 mt-3 leading-none">Status breakdown</p>
                    </div>

                    {{-- Glasses Posted --}}
                    @if($user->role === 'donor')
                        <div class="bg-white border rounded-2xl p-5 h-full flex flex-col">
                            <p class="text-xs text-gray-500">Glasses Posted</p>
                            <p class="text-2xl font-extrabold text-gray-900 mt-2">{{ $stats['glasses_posted'] ?? 0 }}</p>
                            <div class="mt-auto text-xs text-gray-400">Items listed</div>
                        </div>
                    @endif

                </div>
            @endif
            @if(in_array($user->role, ['admin', 'super_admin']))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
                    <div class="bg-white border rounded-2xl p-5">
                        <p class="text-xs text-gray-500">Reviewed Donations</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $stats['reviewed_donations'] ?? 0 }}</p>
                    </div>

                    <div class="bg-white border rounded-2xl p-5">
                        <p class="text-xs text-gray-500">Users Suspended</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $stats['suspended_users'] ?? 0 }}</p>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Actions</p>
                        <p class="text-xs text-gray-500 mt-1">Administrative actions for this user.</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">

                    {{-- Suspend / Unsuspend --}}
                    @if($user->status === 'active')
                        <div x-data="{ openSuspend:false }">
                            <button type="button" @click="openSuspend=true"
                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                                Suspend
                            </button>

                            {{-- Modal --}}
                            <div x-show="openSuspend" x-transition
                                class="fixed inset-0 z-50 flex items-center justify-center" style="display:none;">
                                <div class="absolute inset-0 bg-black/40" @click="openSuspend=false"></div>

                                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border p-6">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-lg font-bold text-gray-900">Suspend user</p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                You are about to suspend <span
                                                    class="font-semibold">{{ $user->name }}</span>.
                                            </p>
                                        </div>
                                        <button type="button" class="p-2 rounded-lg hover:bg-gray-100"
                                            @click="openSuspend=false">✕</button>
                                    </div>

                                    <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}"
                                        class="mt-5 space-y-4">
                                        @csrf

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Reason
                                                (required)</label>
                                            <textarea name="reason" rows="4" required
                                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-200"
                                                placeholder="Write a short reason..."></textarea>
                                            @error('reason')
                                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="flex items-center justify-end gap-3 pt-2">
                                            <button type="button" @click="openSuspend=false"
                                                class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold">
                                                Cancel
                                            </button>

                                            <button type="submit"
                                                onclick="return confirm('Are you sure you want to suspend this user?');"
                                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                                                Confirm suspend
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.users.unsuspend', $user->id) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Unsuspend this user?');"
                                class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                                Unsuspend
                            </button>
                        </form>
                    @endif

                    {{-- Change role (super_admin only) --}}
                    @if(auth()->user()->role === 'super_admin' && $user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.change_role', $user->id) }}"
                            class="flex items-center gap-2">
                            @csrf
                            <select name="role" class="border rounded-xl px-3 py-2 text-sm bg-white">
                                <option value="donor" {{ $user->role === 'donor' ? 'selected' : '' }}>Donor</option>
                                <option value="recipient" {{ $user->role === 'recipient' ? 'selected' : '' }}>Recipient
                                </option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin
                                </option>
                            </select>

                            <button type="submit" onclick="return confirm('Change role for this user?');"
                                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                Update role
                            </button>
                        </form>
                    @endif

                    {{-- Soft delete / restore (super_admin only) --}}
                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']) && $user->id !== auth()->id())
                        @if($user->deleted_at)
                            <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Restore this user?');"
                                    class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                    Restore user
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this user (soft delete)?');"
                                    class="px-4 py-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-sm font-semibold text-red-700">
                                    Delete user
                                </button>
                            </form>
                        @endif
                    @endif

                    {{-- Close open conversations (super_admin only) --}}
                    {{-- @if(auth()->user()->role === 'super_admin' && $hasAnyConversations)
                    @if($hasOpenConversations)
                    <form method="POST" action="{{ route('admin.users.close_conversations', $user->id) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Close all open conversations for this user?');"
                            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                            Close open conversations
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.users.open_conversations', $user->id) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Open all closed conversations for this user?');"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Open conversations
                        </button>
                    </form>
                    @endif
                    @endif --}}

                </div>
            </div>

        </div>
    </div>
</x-app-layout>