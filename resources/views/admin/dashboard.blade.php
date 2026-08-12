<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">Manage platform operations: requests, reports, users, and
                    listings.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                {{-- <a href="{{ route('admin.donation_requests.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">
                    Review donations
                </a>


            </div> --}}
        </div>
    </x-slot>

    @php

        $stats = $stats ?? [
            'pending_donation_requests' => null,
            'open_reports' => null,
            'available_glasses' => null,
            'suspended_users' => null,
        ];
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                {{-- Pending Donation Requests --}}
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500">Pending Donation Requests</p>
                            <p class="text-2xl font-extrabold text-gray-900 mt-1">
                                {{ $stats['pending_donation_requests'] ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">Need review & decision</p>
                        </div>

                        <div
                            class="w-11 h-11 rounded-2xl bg-blue-50 flex items-center justify-center border border-blue-100">
                            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-3H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            </svg>
                        </div>
                    </div>

                    <a href="{{ route('admin.donation_requests.index') }}"
                        class="mt-4 inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        Open requests
                    </a>
                </div>

                {{-- Open Reports --}}
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500">Open Reports</p>
                            <p class="text-2xl font-extrabold text-gray-900 mt-1">
                                {{ $stats['open_reports'] ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">Need investigation</p>
                        </div>

                        <div
                            class="w-11 h-11 rounded-2xl bg-red-50 flex items-center justify-center border border-red-100">
                            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01M10.29 3.86l-8.5 14.73A2 2 0 003.52 21h16.96a2 2 0 001.73-2.41l-8.5-14.73a2 2 0 00-3.42 0z" />
                            </svg>
                        </div>
                    </div>

                    <a href="{{ route('admin.complaints.index') }}"
                        class="mt-4 inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        Open reports
                    </a>
                </div>

                {{-- Available Glasses --}}
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500">Available Listings</p>
                            <p class="text-2xl font-extrabold text-gray-900 mt-1">
                                {{ $stats['available_glasses'] ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">Currently visible to recipients</p>
                        </div>

                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center border border-purple-100">
                            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2 8H8l-2-8m14 0H4" />
                            </svg>
                        </div>
                    </div>

                    <a href="{{ route('admin.glasses.index') }}"
                        class="mt-4 inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        View glasses
                    </a>
                </div>

                {{-- Suspended Users --}}
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500">Suspended Users</p>
                            <p class="text-2xl font-extrabold text-gray-900 mt-1">
                                {{ $stats['suspended_users'] ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">May require review</p>
                        </div>

                        <div
                            class="w-11 h-11 rounded-2xl bg-gray-100 flex items-center justify-center border border-gray-200">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 11c0 2.21-1.79 4-4 4S4 13.21 4 11 5.79 7 8 7s4 1.79 4 4zm8 10v-1a6 6 0 00-6-6h-1" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 010 7.75" />
                            </svg>
                        </div>
                    </div>

                    <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}"
                        class="mt-4 inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        Review users
                    </a>
                </div>
            </div>

            {{-- Main sections --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Primary: actions --}}
                <div class="lg:col-span-2 bg-white border rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Admin tools</p>
                        <p class="text-xs text-gray-500 mt-1">Quick access to the most used sections.</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Donation Requests --}}
                        <a href="{{ route('admin.donation_requests.index') }}"
                            class="group rounded-2xl border p-5 hover:shadow-sm transition bg-white">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Donation Requests</p>
                                    <p class="text-xs text-gray-500 mt-1">Approve / reject confirmations</p>
                                </div>
                                <div
                                    class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100">
                                    <span class="text-blue-700 font-bold text-sm">DR</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-4">
                                Review donor submissions and mark donations as completed.
                            </p>
                            <p class="text-xs font-semibold text-blue-700 mt-3">Open →</p>
                        </a>

                        {{-- Reports --}}
                        <a href="{{ route('admin.complaints.index') }}"
                            class="group rounded-2xl border p-5 hover:shadow-sm transition bg-white">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Reports</p>
                                    <p class="text-xs text-gray-500 mt-1">Investigate & resolve complaints</p>
                                </div>
                                <div
                                    class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center border border-red-100">
                                    <span class="text-red-700 font-bold text-sm">R</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-4">
                                View conversation-based reports and take moderation actions.
                            </p>
                            <p class="text-xs font-semibold text-red-700 mt-3">Open →</p>
                        </a>

                        {{-- Users --}}
                        <a href="{{ route('admin.users.index') }}"
                            class="group rounded-2xl border p-5 hover:shadow-sm transition bg-white">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Users</p>
                                    <p class="text-xs text-gray-500 mt-1">Manage accounts & status</p>
                                </div>
                                <div
                                    class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center border border-green-100">
                                    <span class="text-green-700 font-bold text-sm">U</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-4">
                                Suspend/restore accounts, review profiles, and monitor activity.
                            </p>
                            <p class="text-xs font-semibold text-green-700 mt-3">Open →</p>
                        </a>

                        {{-- Glasses --}}
                        <a href="{{ route('admin.glasses.index') }}"
                            class="group rounded-2xl border p-5 hover:shadow-sm transition bg-white">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Glasses Listings</p>
                                    <p class="text-xs text-gray-500 mt-1">Review statuses & listings</p>
                                </div>
                                <div
                                    class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center border border-purple-100">
                                    <span class="text-purple-700 font-bold text-sm">G</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-4">
                                Track available/reserved/in-contact/pending/donated items.
                            </p>
                            <p class="text-xs font-semibold text-purple-700 mt-3">Open →</p>
                        </a>
                    </div>
                </div>

                {{-- Sidebar: settings --}}
                <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">System</p>
                        <p class="text-xs text-gray-500 mt-1">Configuration & admin-only controls.</p>
                    </div>

                    <div class="p-5 space-y-3">
                        <a href="{{ route('admin.control') }}"
                            class="block rounded-2xl border p-4 hover:bg-gray-50 transition">
                            <p class="text-sm font-semibold text-gray-900">Settings & Control</p>
                            <p class="text-xs text-gray-500 mt-1">Admins, policies, and system options.</p>
                        </a>

                        <div class="rounded-2xl border bg-gray-50 p-4">
                            <p class="text-sm font-semibold text-gray-800">Tips</p>
                            <ul class="mt-2 space-y-1 text-xs text-gray-600 list-disc pl-5">
                                <li>Start with “Donation Requests” daily.</li>
                                <li>Handle “Reports” as priority.</li>
                                <li>Suspended users should be reviewed regularly.</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>