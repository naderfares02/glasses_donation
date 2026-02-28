<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Glasses</h2>
                <p class="text-sm text-gray-500 mt-1">Manage all posted glasses and their statuses.</p>
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

            {{-- Filters --}}
            <div class="bg-white border rounded-2xl shadow-sm p-5">
                <form method="GET" action="{{ route('admin.glasses.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="glasses title, donor name/email..."
                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full border rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                            <option value="">All</option>
                            <option value="available" @selected(request('status') === 'available')>Available</option>
                            <option value="in_contact" @selected(request('status') === 'in_contact')>In contact</option>
                            <option value="reserved" @selected(request('status') === 'reserved')>Reserved</option>
                            <option value="pending_donation" @selected(request('status') === 'pending_donation')>Pending
                                donation</option>
                            <option value="donated" @selected(request('status') === 'donated')>Donated</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                            Apply
                        </button>

                        <a href="{{ route('admin.glasses.index') }}"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800 border">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Total: <span class="font-semibold text-gray-800">{{ $glasses->total() }}</span>
                    </p>

                    <p class="text-xs text-gray-500">
                        Showing {{ $glasses->firstItem() ?? 0 }} - {{ $glasses->lastItem() ?? 0 }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr class="border-b">
                                <th class="text-left font-semibold p-4">Glasses</th>
                                <th class="text-left font-semibold p-4">Donor</th>
                                <th class="text-left font-semibold p-4">Status</th>
                                <th class="text-left font-semibold p-4">Created</th>
                                <th class="text-right font-semibold p-4">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($glasses as $g)
                                @php
                                    $badge = match ($g->status) {
                                        'available' => 'bg-green-50 text-green-700 border-green-200',
                                        'in_contact' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'reserved' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'pending_donation' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'donated' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50/60 transition">
                                    {{-- Glasses --}}
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border shrink-0">
                                                @if($g->primaryImage)
                                                    <img src="{{ asset('storage/' . $g->primaryImage->path) }}"
                                                        class="w-full h-full object-cover" alt="img">
                                                @else
                                                    <div
                                                        class="w-full h-full flex items-center justify-center text-[10px] text-gray-500">
                                                        No image
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-800 truncate">
                                                    {{ $g->title ?? 'Glasses' }}
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">
                                                    #{{ $g->id }}
                                                    @if($g->active_contact_request_id)
                                                        • Active request: {{ $g->active_contact_request_id }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Donor --}}
                                    <td class="p-4">
                                        <p class="font-semibold text-gray-800">
                                            @if($g->user)
                                                <a class="hover:underline" href="{{ route('admin.users.show', $g->user->id) }}">
                                                    {{ $g->user->name }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $g->user?->email ?? '' }}</p>
                                    </td>

                                    {{-- Status --}}
                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                            {{ strtoupper($g->status ?? '—') }}
                                        </span>
                                    </td>

                                    {{-- Created --}}
                                    <td class="p-4 text-gray-600">
                                        <p>{{ $g->created_at?->format('Y-m-d') }}</p>
                                        <p class="text-xs text-gray-500">Updated: {{ $g->updated_at?->format('Y-m-d') }}</p>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="p-4 text-right">
                                        <a href="{{ route('admin.glasses.show', $g->id) }}"
                                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white inline-block">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-500">
                                        No glasses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t bg-white">
                    {{ $glasses->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>