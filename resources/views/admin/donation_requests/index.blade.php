<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Donation Requests</h2>
                <p class="text-sm text-gray-500 mt-1">Review and confirm completed donations.</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white border rounded-2xl shadow-sm p-5">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="donor/recipient name, glasses title..."
                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full border rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                            <option value="">All</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                            Apply
                        </button>
                        <a href="{{ route('admin.donation_requests.index') }}"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800 border">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Total: <span class="font-semibold text-gray-800">{{ $requests->total() }}</span>
                    </p>

                    <p class="text-xs text-gray-500">
                        Showing {{ $requests->firstItem() ?? 0 }} - {{ $requests->lastItem() ?? 0 }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr class="border-b">
                                <th class="text-left font-semibold p-4">Glasses</th>
                                <th class="text-left font-semibold p-4">Donor</th>
                                <th class="text-left font-semibold p-4">Recipient</th>
                                <th class="text-left font-semibold p-4">Delivered</th>
                                <th class="text-left font-semibold p-4">Status</th>
                                <th class="text-right font-semibold p-4">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($requests as $r)
                                @php
                                    $badge = match ($r->status) {
                                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'approved' => 'bg-green-50 text-green-700 border-green-200',
                                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border shrink-0">
                                                @if($r->glasses?->primaryImage)
                                                    <a href="{{ route('admin.glasses.show', $r->glasses->id) }}">
                                                        <img src="{{ asset('storage/' . $r->glasses->primaryImage->path) }}"
                                                            class="w-full h-full object-cover" alt="img">
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-800 truncate">
                                                    <a href="{{ route('admin.glasses.show', $r->glasses->id) }}">
                                                        {{ $r->glasses->title ?? 'Glasses' }}
                                                    </a>
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">
                                                    #{{ $r->id }} • Conversation: {{ $r->conversation_id ?? '—' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-semibold text-gray-800"> <a
                                                href="{{ route('admin.users.show', $r->donor->id) }}">
                                                {{ $r->donor->name ?? '—' }}</a></p>
                                        <p class="text-xs text-gray-500">{{ $r->donor->email ?? '' }}</p>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-semibold text-gray-800"><a
                                                href="{{ route('admin.users.show', $r->recipient->id) }}">{{ $r->recipient->name ?? '—' }}</a>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $r->recipient->email ?? '' }}</p>
                                    </td>

                                    <td class="p-4">
                                        <p class="text-gray-800">
                                            {{ $r->delivered_date ? \Carbon\Carbon::parse($r->delivered_date)->format('Y-m-d') : '—' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Submitted: {{ $r->created_at?->format('Y-m-d') }}
                                        </p>
                                    </td>

                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                            {{ strtoupper($r->status) }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-right">
                                        <a href="{{ route('admin.donation_requests.show', $r->id) }}"
                                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white inline-block">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-gray-500">
                                        No donation requests found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t bg-white">
                    {{ $requests->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>