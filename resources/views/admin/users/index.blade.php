<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Users</h2>
                <p class="text-sm text-gray-500 mt-1">Manage donors, recipients, and admins.</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters Card --}}
            <div class="bg-white border rounded-2xl shadow-sm p-5">
                <form method="GET" action="{{ route('admin.users.index') }}"
                    class="flex flex-col lg:flex-row gap-3 lg:items-end">

                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Search by name or email..."
                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                    </div>

                    @php
                        $pill = fn($key) => route('admin.users.index', array_filter([
                            'role' => $key !== 'all' ? $key : null,
                            'status' => request('status') !== 'all' ? request('status') : null,
                            'q' => request('q') ?: null,
                            'deleted' => request('deleted'), // إذا عندك فلتر deleted
                            'sort' => request('sort'),    // إذا عندك sort
                        ], fn($v) => !is_null($v) && $v !== ''));
                    @endphp

                    <div class="w-full lg:w-52">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                        <select name="role"
                            class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                            <option value="all" {{ $role === 'all' ? 'selected' : '' }}>All</option>
                            <option value="donor" {{ $role === 'donor' ? 'selected' : '' }}>Donor</option>
                            <option value="recipient" {{ $role === 'recipient' ? 'selected' : '' }}>Recipient</option>
                            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin
                            </option>
                        </select>
                    </div>

                    <div class="w-full lg:w-52">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-5 py-3 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                            Apply
                        </button>

                        <a href="{{ route('admin.users.index') }}"
                            class="px-5 py-3 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 border">
                            Reset
                        </a>
                    </div>
                </form>

                {{-- Role pills --}}
                <div class="mt-4 flex flex-wrap gap-2">


                    <a href="{{ $pill('all') }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold border
                              {{ $role === 'all' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                        All ({{ $counts['all'] }})
                    </a>

                    <a href="{{ $pill('donor') }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold border
                              {{ $role === 'donor' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                        Donors ({{ $counts['donor'] }})
                    </a>

                    <a href="{{ $pill('recipient') }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold border
                              {{ $role === 'recipient' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                        Recipients ({{ $counts['recipient'] }})
                    </a>

                    @if (auth()->user()->role != 'admin')
                        <a href="{{ $pill('admin') }}"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold border
                                                        {{ $role === 'admin' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                            Admins ({{ $counts['admin'] }})
                        </a>
                    @endif
                    {{-- <a href="{{ $pill('super_admin') }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold border
                              {{ $role === 'super_admin' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                        Super Admins ({{ $counts['super_admin'] }})
                    </a> --}}
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-800">Users list</p>
                    <p class="text-xs text-gray-500">
                        Total: <span class="font-semibold">{{ $users->total() }}</span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr class="text-left">
                                <th class="p-4 font-semibold">User</th>
                                <th class="p-4 font-semibold">Role</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Joined</th>
                                <th class="p-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($users as $u)
                                @php
                                    $isActive = isset($u->is_active) ? (bool) $u->is_active : (isset($u->active) ? (bool) $u->active : true);

                                    $roleBadge = match ($u->role) {
                                        'donor' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'recipient' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'admin' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        'super_admin' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };

                                    $statusBadge = $isActive
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : 'bg-red-50 text-red-700 border-red-200';
                                @endphp

                                <tr class="hover:bg-gray-50/60">
                                    {{-- User --}}
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            {{-- Avatar --}}
                                            <div
                                                class="w-10 h-10 rounded-xl overflow-hidden border bg-gray-100 flex items-center justify-center shrink-0">
                                                @if($u->avatar)
                                                    <img src="{{ asset('storage/' . $u->avatar) }}"
                                                        class="w-full h-full object-cover" alt="avatar">
                                                @else
                                                    <div
                                                        class="w-full h-full flex items-center justify-center text-sm font-bold text-gray-700">
                                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 truncate">{{ $u->name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $u->email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Role --}}
                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border {{ $roleBadge }}">
                                            {{ strtoupper($u->role) }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="p-4">
                                        @php
                                            $statusBadge = match ($u->status) {
                                                'active' => 'bg-green-50 text-green-700 border-green-200',
                                                'suspended' => 'bg-red-50 text-red-700 border-red-200',
                                                default => 'bg-gray-100 text-gray-700 border-gray-200',
                                            };
                                        @endphp

                                        <span
                                            class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border {{ $statusBadge }}">
                                            {{ strtoupper($u->status) }}
                                        </span>
                                    </td>

                                    {{-- Joined --}}
                                    <td class="p-4 text-gray-600">
                                        {{ $u->created_at?->format('Y-m-d') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="p-4">
                                        <div x-data="actionMenu()" class="relative flex justify-end">
                                            <button type="button" x-ref="btn" @click="toggle()"
                                                class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold">
                                                •••
                                            </button>

                                            {{-- نرسل القائمة خارج الجدول --}}
                                            <template x-teleport="body">
                                                <div x-show="open" x-transition.opacity @click.outside="close()"
                                                    @keydown.escape.window="close()" class="fixed z-[9999]" :style="style"
                                                    style="display:none;">

                                                    <div x-ref="menu"
                                                        class="w-48 rounded-xl border bg-white shadow-lg overflow-hidden">
                                                        <a href="{{ route('admin.users.show', $u->id) }}"
                                                            class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                                            View
                                                        </a>

                                                        <a href="{{ route('admin.users.edit', $u->id) }}"
                                                            class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                                            Edit
                                                        </a>

                                                        <div class="border-t"></div>
                                                        @if($u->status === 'active')
                                                            <div x-data="{ openSuspend: false }" class="relative">

                                                                {{-- زر Suspend --}}
                                                                <button type="button" @click="openSuspend = true"
                                                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 text-red-600">
                                                                    Suspend
                                                                </button>

                                                                {{-- Modal --}}
                                                                <div x-show="openSuspend" x-transition
                                                                    class="fixed inset-0 z-50 flex items-center justify-center">

                                                                    {{-- الخلفية --}}
                                                                    <div class="absolute inset-0 bg-black/40"
                                                                        @click="openSuspend = false"></div>

                                                                    {{-- الصندوق --}}
                                                                    <div
                                                                        class="relative bg-white w-full max-w-md rounded-2xl shadow-xl p-6">

                                                                        <div class="flex items-start justify-between">
                                                                            <div>
                                                                                <h3 class="text-lg font-bold text-gray-800">
                                                                                    Suspend user
                                                                                </h3>
                                                                                <p class="text-sm text-gray-600 mt-1">
                                                                                    Are you sure you want to suspend this user?
                                                                                </p>
                                                                            </div>

                                                                            <button @click="openSuspend = false"
                                                                                class="p-2 hover:bg-gray-100 rounded-lg">
                                                                                ✕
                                                                            </button>
                                                                        </div>

                                                                        <form method="POST"
                                                                            action="{{ route('admin.users.suspend', $u->id) }}"
                                                                            class="mt-5 space-y-4">
                                                                            @csrf

                                                                            <div>
                                                                                <label
                                                                                    class="block text-sm font-semibold text-gray-700 mb-1">
                                                                                    Suspension reason
                                                                                </label>
                                                                                <textarea name="reason" rows="3" required
                                                                                    class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-200"
                                                                                    placeholder="Write the reason for suspension..."></textarea>
                                                                            </div>

                                                                            <div class="flex justify-end gap-3 mt-6">
                                                                                <button type="button"
                                                                                    @click="openSuspend = false"
                                                                                    class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                                                                                    Cancel
                                                                                </button>

                                                                                <button type="submit"
                                                                                    class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                                                                                    Confirm suspend
                                                                                </button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <form method="POST"
                                                                action="{{ route('admin.users.unsuspend', $u->id) }}">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 text-green-700">
                                                                    Unsuspend
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-500">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t bg-gray-50">
                    {{ $users->links() }}
                </div>
            </div>

        </div>
    </div>
    <script>
        function actionMenu() {
            return {
                open: false,
                style: '',
                gap: 6, // المسافة بين الزر والقائمة

                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.$nextTick(() => {
                            // انتظر frame عشان x-show يظهرها فعلاً ونقدر نقيسها
                            requestAnimationFrame(() => this.reposition());
                        });
                    }
                },

                close() { this.open = false; },

                reposition() {
                    const btn = this.$refs.btn;
                    if (!btn) return;

                    const r = btn.getBoundingClientRect();

                    // قياس الحجم الحقيقي للقائمة
                    const menuEl = this.$refs.menu;
                    const menuW = menuEl ? menuEl.getBoundingClientRect().width : 192;
                    const menuH = menuEl ? menuEl.getBoundingClientRect().height : 150;

                    // افتراضي: تحت الزر ومحاذاة يمين
                    let top = r.bottom + this.gap;
                    let left = r.right - menuW;

                    // اضبط اليسار ضمن الشاشة
                    left = Math.max(8, Math.min(left, window.innerWidth - menuW - 8));

                    // لو ما في مساحة تحت، اطلع فوق (قريب من الزر)
                    const spaceBelow = window.innerHeight - r.bottom;
                    const spaceAbove = r.top;

                    if (spaceBelow < (menuH + this.gap) && spaceAbove > (menuH + this.gap)) {
                        top = r.top - menuH - this.gap;
                    }

                    // اضبط top ضمن الشاشة
                    top = Math.max(8, Math.min(top, window.innerHeight - menuH - 8));

                    this.style = `top:${top}px; left:${left}px;`;
                },

                init() {
                    // إعادة تموضع أثناء السكرول/الريسايز
                    window.addEventListener('scroll', () => { if (this.open) this.reposition(); }, true);
                    window.addEventListener('resize', () => { if (this.open) this.reposition(); });
                }
            }
        }
    </script>
</x-app-layout>