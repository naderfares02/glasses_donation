@php
    $status = $glasses->status;

    $statusBadge = match ($status) {
        'available' => 'bg-green-50 text-green-700 border-green-200',
        'reserved' => 'bg-purple-50 text-purple-700 border-purple-200',
        'in_contact' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'pending_donation' => 'bg-amber-50 text-amber-800 border-amber-200',
        'donated' => 'bg-blue-50 text-blue-700 border-blue-200',
        default => 'bg-gray-50 text-gray-700 border-gray-200',
    };

    $extras = $glasses->images?->where('is_primary', false) ?? collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Glasses Details (Admin View)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Full listing overview and moderation controls
                </p>
            </div>

            <a href="{{ route('admin.glasses.index') }}"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Top Summary Card --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $glasses->title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <span class="px-3 py-1 rounded-full border text-xs font-semibold {{ $statusBadge }}">
                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                            </span>

                            <span
                                class="px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 text-gray-700 border-gray-200">
                                {{ strtoupper($glasses->condition) }}
                            </span>
                        </div>
                    </div>

                    {{-- Admin Quick Actions --}}
                    {{-- <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.glasses.edit', $glasses->id) }}"
                            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('admin.glasses.delete', $glasses->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                                Delete
                            </button>
                        </form> --}}
                    </div>
                </div>

                {{-- Main Layout --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-0">

                    {{-- Images --}}
                    <div class="lg:col-span-2 p-6 border-r space-y-4">

                        <div class="rounded-2xl border overflow-hidden">
                            @if($glasses->primaryImage)
                                <img src="{{ asset('storage/' . $glasses->primaryImage->path) }}"
                                    class="w-full h-[350px] object-cover">
                            @else
                                <div class="h-[350px] flex items-center justify-center text-gray-500">
                                    No main image
                                </div>
                            @endif
                        </div>

                        @if($extras->count())
                            <div class="grid grid-cols-4 gap-3">
                                @foreach($extras as $img)
                                    <img src="{{ asset('storage/' . $img->path) }}"
                                        class="rounded-xl border object-cover h-20 w-full">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Admin Info Sidebar --}}
                    <div class="p-6 space-y-5 bg-gray-50">

                        <div class="bg-white border rounded-2xl p-4">
                            <p class="text-xs text-gray-500">Donor</p>
                            <p class="font-semibold text-gray-900 mt-1">
                                <a
                                    href="{{ route('admin.users.show', $glasses->user->id) }}">{{ $glasses->donor->name }}</a>
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $glasses->donor->email }}
                            </p>
                        </div>

                        <div class="bg-white border rounded-2xl p-4">
                            <p class="text-xs text-gray-500">Created At</p>
                            <p class="font-semibold mt-1">
                                {{ $glasses->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>

                        <div class="bg-white border rounded-2xl p-4">
                            <p class="text-xs text-gray-500">Contact Requests</p>
                            <p class="font-semibold mt-1">
                                {{ $glasses->contactRequests()->count() }}
                            </p>
                        </div>

                        <div class="bg-white border rounded-2xl p-4">
                            <p class="text-xs text-gray-500">Active Conversation</p>
                            <p class="font-semibold mt-1">
                                {{ $glasses->conversations()->count() > 0 ? 'Yes' : 'No' }}
                            </p>
                        </div>

                    </div>
                </div>

                {{-- Description --}}
                <div class="bg-white border rounded-3xl shadow-sm">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold">Description</p>
                    </div>
                    <div class="p-6 text-gray-700 leading-relaxed">
                        {{ $glasses->description ?: 'No description provided.' }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>