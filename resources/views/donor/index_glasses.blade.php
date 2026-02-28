<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Glasses</h2>
                <p class="text-sm text-gray-500 mt-1">Manage your listings, update status, and track donations.</p>
            </div>

            <a href="{{ route('donor.glasses.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">
                <span class="text-base leading-none">＋</span>
                Add New Glasses
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                    <p class="font-semibold text-sm">Success</p>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                    <p class="font-semibold text-sm">Error</p>
                    <p class="text-sm mt-1">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50 flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Your listings</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Total: <span class="font-semibold text-gray-800">{{ $glasses->total() }}</span>
                        </p>
                    </div>

                    <form method="GET" class="w-full lg:w-auto flex flex-col sm:flex-row gap-3 sm:items-center">
                        <div class="relative w-full sm:w-72">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by title..."
                                class="w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>

                        <select name="status"
                            class="w-full sm:w-56 border rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                            <option value="">All statuses</option>
                            <option value="available" @selected(request('status') === 'available')>Available</option>
                            <option value="reserved" @selected(request('status') === 'reserved')>Reserved</option>
                            <option value="in_contact" @selected(request('status') === 'in_contact')>In contact</option>
                            <option value="pending_donation" @selected(request('status') === 'pending_donation')>Pending
                                donation</option>
                            <option value="donated" @selected(request('status') === 'donated')>Donated</option>
                        </select>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                Apply
                            </button>
                            <a href="{{ route('donor.glasses.index') }}"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white hover:bg-gray-50 border text-gray-800">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Cards --}}
                <div class="p-5">
                    @if($glasses->count() === 0)
                        <div class="rounded-3xl border bg-white p-12 text-center">
                            <p class="text-gray-900 font-semibold">No glasses yet</p>
                            <p class="text-sm text-gray-500 mt-2">Add your first listing and start helping people.</p>
                            <a href="{{ route('donor.glasses.create') }}"
                                class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                ＋ Add New Glasses
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                            @foreach($glasses as $item)
                                @php
                                    $status = $item->status;

                                    $badge = match ($status) {
                                        'available' => 'bg-green-50 text-green-700 border-green-200',
                                        'reserved' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'in_contact' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'pending_donation' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'donated' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };

                                    $condBadge = ($item->condition === 'new')
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : 'bg-gray-50 text-gray-700 border-gray-200';
                                @endphp

                                <div class="bg-white border rounded-3xl shadow-sm overflow-hidden hover:shadow-md transition">
                                    {{-- Image --}}
                                    <div class="relative">
                                        <div class="h-44 bg-gray-100">
                                            @if($item->primaryImage)
                                                <img src="{{ asset('storage/' . $item->primaryImage->path) }}"
                                                    class="w-full h-full object-cover" alt="Glasses image">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-sm text-gray-500">
                                                    No image
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Status badge top-left --}}
                                        <div class="absolute top-3 left-3">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                                            </span>
                                        </div>
                                        @if ($item->status !== 'donated')

                                            {{-- Actions menu top-right --}}
                                            <div class="absolute top-3 right-3" x-data="{ open:false }"
                                                @keydown.escape.window="open=false">
                                                <button type="button" @click="open=!open"
                                                    class="w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-white/90 hover:bg-white border shadow-sm">
                                                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </button>
                                                <div x-cloak x-show="open" @click.outside="open=false" x-transition
                                                    class="absolute right-0 mt-2 w-44 bg-white border rounded-2xl shadow-lg overflow-hidden z-50">

                                                    <div class="border-t"></div>

                                                    <form action="{{ route('donor.glasses.destroy', $item->id) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                    {{-- Content --}}
                                    <div class="p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-base font-extrabold text-gray-900 truncate">{{ $item->title }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">#{{ $item->id }}</p>
                                            </div>

                                            <span
                                                class="shrink-0 inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $condBadge }}">
                                                {{ ucfirst($item->condition) }}
                                            </span>
                                        </div>

                                        <div class="mt-4 flex items-center justify-between text-sm">
                                            <p class="text-gray-500">Added</p>
                                            <p class="font-semibold text-gray-800">
                                                {{ $item->created_at?->format('Y-m-d') ?? '—' }}
                                            </p>
                                        </div>

                                        {{-- Quick CTA row --}}
                                        @if ($item->status === 'donated')
                                            <div class="mt-4 grid grid-cols-1">
                                                <a href="{{ route('donor.glasses.show', $item->id) }}"
                                                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                                    View
                                                </a>

                                            </div>
                                        @else
                                            <div class="mt-4 grid grid-cols-2 gap-2">
                                                <a href="{{ route('donor.glasses.show', $item->id) }}"
                                                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                                    View
                                                </a>
                                                <a href="{{ route('donor.glasses.edit', $item->id) }}"
                                                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                                    Edit
                                                </a>
                                            </div>

                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $glasses->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>