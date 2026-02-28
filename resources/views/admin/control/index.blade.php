<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Control</h2>
                <p class="text-sm text-gray-500 mt-1">Manage admins, policies, and system settings.</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Top summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Pending donation reviews --}}
                <a href="{{ route('admin.donation_requests.index', ['status' => 'pending']) }}"
                    class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition block">
                    <p class="text-xs text-gray-500">Pending Donation Reviews</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">
                        {{ $counts['pending_donations'] ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Require admin approval</p>
                </a>

                {{-- Suspended users --}}
                <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}"
                    class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition block">
                    <p class="text-xs text-gray-500">Suspended Users</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">
                        {{ $counts['suspended'] ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Accounts currently restricted</p>
                </a>

                {{-- System status --}}
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-xs text-gray-500">System Status</p>
                    <p class="text-2xl font-extrabold mt-1
            {{ $isDown ? 'text-red-600' : 'text-green-600' }}">
                        {{ $isDown ? 'Maintenance' : 'Live' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ $isDown ? 'Public access disabled' : 'Platform operating normally' }}
                    </p>
                </div>

            </div>

            {{-- Main sections --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Policies & Legal --}}
                <div class="lg:col-span-2 bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Policies & Legal Pages</p>
                            <p class="text-xs text-gray-500 mt-1">Terms of Use & Privacy Policy.</p>
                        </div>
                        {{--
                        @if($isSuperAdmin)
                        <a href="{{ route('admin.legal.index') }}"
                            class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                            Manage all →
                        </a>
                        @endif --}}
                    </div>

                    <div class="p-5 space-y-4">

                        {{-- Terms card --}}
                        @php
                            $terms = $legal['terms'] ?? null;
                            $privacy = $legal['privacy'] ?? null;

                            $badge = function ($page) {
                                if (!$page)
                                    return 'bg-gray-50 text-gray-700 border-gray-200';
                                return $page->published_at
                                    ? 'bg-green-50 text-green-700 border-green-200'
                                    : 'bg-amber-50 text-amber-800 border-amber-200';
                            };

                            $badgeText = function ($page) {
                                if (!$page)
                                    return 'MISSING';
                                return $page->published_at ? 'PUBLISHED' : 'DRAFT';
                            };
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Terms --}}
                            <div class="border rounded-2xl p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-500">TERMS</p>
                                        <p class="font-bold text-gray-900 mt-1">
                                            {{ $terms?->title ?? 'Terms of Use' }}
                                        </p>
                                    </div>

                                    <span
                                        class="text-xs font-semibold px-3 py-1 rounded-full border {{ $badge($terms) }}">
                                        {{ $badgeText($terms) }}
                                    </span>
                                </div>

                                <div class="mt-4 text-xs text-gray-500 space-y-1">
                                    <p>
                                        Last update:
                                        <span class="font-semibold text-gray-800">
                                            {{ $terms?->updated_at?->format('Y-m-d H:i') ?? '—' }}
                                        </span>
                                    </p>
                                    <p>
                                        Published:
                                        <span class="font-semibold text-gray-800">
                                            {{ $terms?->published_at?->format('Y-m-d H:i') ?? '—' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="mt-5 flex items-center justify-between">
                                    <a href="{{ route('terms') }}"
                                        class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                                        View →
                                    </a>

                                    @if($isSuperAdmin && $terms)
                                        <a href="{{ route('admin.legal.edit', $terms->id) }}"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                            Edit
                                        </a>
                                    @elseif(!$isSuperAdmin)
                                        <span class="text-xs text-gray-500">Only super admin can edit</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Privacy --}}
                            <div class="border rounded-2xl p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-500">PRIVACY</p>
                                        <p class="font-bold text-gray-900 mt-1">
                                            {{ $privacy?->title ?? 'Privacy Policy' }}
                                        </p>
                                    </div>

                                    <span
                                        class="text-xs font-semibold px-3 py-1 rounded-full border {{ $badge($privacy) }}">
                                        {{ $badgeText($privacy) }}
                                    </span>
                                </div>

                                <div class="mt-4 text-xs text-gray-500 space-y-1">
                                    <p>
                                        Last update:
                                        <span class="font-semibold text-gray-800">
                                            {{ $privacy?->updated_at?->format('Y-m-d H:i') ?? '—' }}
                                        </span>
                                    </p>
                                    <p>
                                        Published:
                                        <span class="font-semibold text-gray-800">
                                            {{ $privacy?->published_at?->format('Y-m-d H:i') ?? '—' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="mt-5 flex items-center justify-between">
                                    <a href="{{ route('privacy') }}"
                                        class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                                        View →
                                    </a>

                                    @if($isSuperAdmin && $privacy)
                                        <a href="{{ route('admin.legal.edit', $privacy->id) }}"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                            Edit
                                        </a>
                                    @elseif(!$isSuperAdmin)
                                        <span class="text-xs text-gray-500">Only super admin can edit</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!$isSuperAdmin)
                            <div class="mt-2 p-4 rounded-xl border bg-amber-50 border-amber-200">
                                <p class="text-sm font-semibold text-amber-900">Limited access</p>
                                <p class="text-xs text-amber-800 mt-1">
                                    You can view policies, but only <span class="font-semibold">Super Admin</span> can edit
                                    them.
                                </p>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Admin Tools</p>
                        <p class="text-xs text-gray-500 mt-1">Quick controls & shortcuts.</p>
                    </div>

                    <div class="p-5 space-y-3">

                        <a href="{{ route('admin.settings.index') }}"
                            class="block p-4 rounded-2xl border hover:bg-gray-50 transition">
                            <p class="text-sm font-bold text-gray-900">System Settings</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Coming next: global settings, limits, moderation.
                            </p>
                        </a>

                        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
                            class="block p-4 rounded-2xl border hover:bg-gray-50 transition">
                            <p class="text-sm font-bold text-gray-900">Manage Admins</p>
                            <p class="text-xs text-gray-500 mt-1">View admins & super admins.</p>
                        </a>

                        <a href="{{ route('admin.users.index') }}"
                            class="block p-4 rounded-2xl border hover:bg-gray-50 transition">
                            <p class="text-sm font-bold text-gray-900">User Management</p>
                            <p class="text-xs text-gray-500 mt-1">Suspend users, view details.</p>
                        </a>


                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>