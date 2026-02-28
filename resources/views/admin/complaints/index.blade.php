<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reports Management
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Review and manage user complaints.
                </p>
            </div>

            {{-- Summary --}}
            <div class="text-sm text-gray-600">
                Total: <span class="font-semibold">{{ $complaints->total() }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <div class="bg-white border rounded-2xl shadow-sm p-5">
                <form method="GET" class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">

                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">

                        {{-- Search --}}
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="Search by user name or reason..."
                            class="w-full sm:w-72 border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200">

                        {{-- Status Filter --}}
                        <select name="status"
                            class="w-full sm:w-56 border rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-200">
                            <option value="all">All statuses</option>
                            <option value="open" @selected(request('status') === 'open')>Open</option>
                            <option value="reviewing" @selected(request('status') === 'reviewing')>Reviewing</option>
                            <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                            <option value="dismissed" @selected(request('status') === 'dismissed')>Dismissed</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white">
                            Apply
                        </button>

                        <a href="{{ route('admin.complaints.index') }}"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 border text-gray-800">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Reports Table --}}
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b text-gray-600">
                            <tr>
                                <th class="p-4 text-left font-semibold">ID</th>
                                <th class="p-4 text-left font-semibold">Reporter</th>
                                <th class="p-4 text-left font-semibold">Against</th>
                                <th class="p-4 text-left font-semibold">Reason</th>
                                <th class="p-4 text-left font-semibold">Status</th>
                                <th class="p-4 text-left font-semibold">Created</th>
                                <th class="p-4 text-right font-semibold">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($complaints as $complaint)

                                @php
                                    $badge = match ($complaint->status) {
                                        'open' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'reviewing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'resolved' => 'bg-green-50 text-green-700 border-green-200',
                                        'dismissed' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-semibold text-gray-800">
                                        #{{ $complaint->id }}
                                    </td>

                                    <td class="p-4 text-gray-700">
                                        {{ $complaint->reporter?->name }}
                                    </td>

                                    <td class="p-4 text-gray-700">
                                        {{ $complaint->reportedUser?->name }}
                                    </td>

                                    <td class="p-4 text-gray-600">
                                        {{ ucfirst(str_replace('_', ' ', $complaint->reason)) }}
                                    </td>

                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                            {{ strtoupper($complaint->status) }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-gray-500">
                                        {{ $complaint->created_at->format('Y-m-d') }}
                                    </td>

                                    <td class="p-4 text-right">
                                        <a href="{{ route('admin.complaints.show', $complaint->id) }}"
                                            class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white">
                                            View
                                        </a>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center text-gray-500">
                                        No reports found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="p-5 border-t bg-white">
                    {{ $complaints->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>