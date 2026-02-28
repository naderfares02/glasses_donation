@php
    $status = $glasses->status ?? 'unknown';

    $statusBadge = match ($status) {
        'available' => 'bg-green-50 text-green-700 border-green-200',
        'reserved' => 'bg-purple-50 text-purple-700 border-purple-200',
        'in_contact' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'pending_donation' => 'bg-amber-50 text-amber-800 border-amber-200',
        'donated' => 'bg-blue-50 text-blue-700 border-blue-200',
        default => 'bg-gray-50 text-gray-700 border-gray-200',
    };

    $hasActive = !empty($glasses->active_contact_request_id);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Contact Requests</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Manage requests for this listing and start a chat after accepting.
                </p>
            </div>

            <a href="{{ route('donor.glasses.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                ← Back to My Glasses
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                    <p class="font-semibold text-sm">Success</p>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                    <p class="font-semibold text-sm">Error</p>
                    <p class="text-sm mt-1">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Glasses summary card --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">Glasses</p>
                        <p class="text-lg font-extrabold text-gray-900 mt-1 truncate">
                            <a href="{{ route('donor.glasses.show', $glasses->id) }}">
                                {{ $glasses->title }}
                            </a>
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $statusBadge }}">
                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                            </span>

                            @if($hasActive)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold bg-blue-50 text-blue-700 border-blue-200">
                                    ACTIVE REQUEST SELECTED
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 text-gray-700 border-gray-200">
                                    NO ACTIVE REQUEST
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-sm text-gray-600">
                        <p class="text-xs text-gray-500">Total requests</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">
                            {{ $requests->count() }}
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl border bg-gray-50 p-4">
                        <p class="text-sm text-gray-700 font-semibold">How it works</p>
                        <p class="text-sm text-gray-600 mt-1">
                            When you accept a request, this glasses will be reserved for that recipient to avoid
                            conflicts.
                            You can then message them from the chat button.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Requests --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Requests</p>
                        <p class="text-xs text-gray-500 mt-1">Accept one request only, or review all.</p>
                    </div>
                </div>

                @if($requests->count() === 0)
                    <div class="p-12 text-center text-gray-600">
                        <p class="font-semibold">No contact requests yet.</p>
                        <p class="text-sm text-gray-500 mt-2">When a recipient requests contact, it will appear here.</p>
                    </div>
                @else
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($requests as $request)
                            @php
                                $rStatus = $request->status;

                                $badge = match ($rStatus) {
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'accepted' => 'bg-green-50 text-green-700 border-green-200',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    'closed' => 'bg-gray-50 text-gray-700 border-gray-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                };

                                $canDecide = ($rStatus === 'pending' && !$hasActive);
                            @endphp

                            <div class="rounded-3xl border bg-white overflow-hidden">
                                <div class="p-5 border-b bg-gray-50 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-extrabold text-gray-900 truncate">
                                            {{ $request->recipient->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1 truncate">
                                            {{ $request->recipient->email }}
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                        {{ strtoupper($rStatus) }}
                                    </span>
                                </div>

                                <div class="p-5">
                                    <div class="flex items-center justify-between text-sm">
                                        <p class="text-gray-500">Requested at</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $request->created_at?->format('Y-m-d H:i') ?? '—' }}
                                        </p>
                                    </div>

                                    <div class="mt-5 flex flex-wrap gap-2">
                                        @if($canDecide)
                                            {{-- Accept --}}
                                            <form method="POST" action="{{ route('donor.requests.accept', $request->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    onclick="return confirm('Accept this request? The glasses will be reserved for this recipient.');"
                                                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                                                    Accept
                                                </button>
                                            </form>

                                            {{-- Reject --}}
                                            <form method="POST" action="{{ route('donor.requests.reject', $request->id) }}">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Reject this request?');"
                                                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold">
                                                    Reject
                                                </button>
                                            </form>

                                        @elseif($rStatus === 'accepted')
                                            {{-- Message --}}
                                            <a href="{{ route('donor.chats.index', ['conversation' => $request->conversation]) }}"
                                                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                                Message Recipient
                                            </a>

                                            <span class="text-xs text-gray-500 self-center">
                                                This request is the active one.
                                            </span>

                                        @else
                                            <span class="text-sm text-gray-500">No actions available.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>