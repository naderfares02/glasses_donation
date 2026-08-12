<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Conversation Review</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Glasses:
                    <span class="font-semibold">{{ $conversation->glasses->title ?? '—' }}</span>
                    <span class="mx-2">•</span>
                    Conversation #{{ $conversation->id }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    ← Back
                </a>


            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row gap-6 md:items-center md:justify-between">

                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-100 border shrink-0">
                            @if($conversation->glasses?->primaryImage)
                                <img src="{{ asset('storage/' . $conversation->glasses->primaryImage->path) }}"
                                    class="w-full h-full object-cover" alt="Glasses">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs text-gray-500">
                                    No image
                                </div>
                            @endif
                        </div>

                        <div>
                            <p class="text-lg font-bold text-gray-800">
                                {{ $conversation->glasses->title ?? 'Glasses' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                Created: <span
                                    class="font-semibold">{{ $conversation->created_at?->format('Y-m-d H:i') }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-3">

                            <span class="text-xs font-semibold px-3 py-1 rounded-full border
        {{ $conversation->status === 'open'
    ? 'bg-green-50 text-green-700 border-green-200'
    : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                {{ strtoupper($conversation->status) }}
                            </span>

                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                                                    <form method="POST" action="{{ route('admin.conversations.toggle', $conversation->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-xs font-semibold px-3 py-1 rounded-lg border
                                        {{ $conversation->status === 'open'
                                ? 'bg-red-600 text-white border-red-600 hover:bg-red-700'
                                : 'bg-green-600 text-white border-green-600 hover:bg-green-700' }}">

                                                            {{ $conversation->status === 'open' ? 'Close' : 'Reopen' }}
                                                        </button>
                                                    </form>
                            @endif

                        </div>

                        <span
                            class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                            Admin view (read-only)
                        </span>
                    </div>

                </div>
            </div>

            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <p class="text-sm font-semibold text-gray-800 mb-4">Participants</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-2xl p-4 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-500 mb-3">DONOR</p>
                        <div class="flex items-center gap-3">
                            @php $donor = $conversation->donor; @endphp

                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden bg-white border flex items-center justify-center shrink-0">
                                @if($donor?->avatar)
                                    <img src="{{ asset('storage/' . $donor->avatar) }}" class="w-full h-full object-cover"
                                        alt="donor">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center text-sm font-bold text-gray-700">
                                        {{ strtoupper(substr($donor?->name ?? 'D', 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 truncate">{{ $donor->name ?? '—' }}</p>
                                <p class="text-sm text-gray-600 truncate">{{ $donor->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-2xl p-4 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-500 mb-3">RECIPIENT</p>
                        <div class="flex items-center gap-3">
                            @php $recipient = $conversation->recipient; @endphp

                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden bg-white border flex items-center justify-center shrink-0">
                                @if($recipient?->avatar)
                                    <img src="{{ asset('storage/' . $recipient->avatar) }}"
                                        class="w-full h-full object-cover" alt="recipient">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center text-sm font-bold text-gray-700">
                                        {{ strtoupper(substr($recipient?->name ?? 'R', 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 truncate">{{ $recipient->name ?? '—' }}</p>
                                <p class="text-sm text-gray-600 truncate">{{ $recipient->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-800">Messages</p>
                    <p class="text-xs text-gray-500">
                        Total: <span class="font-semibold">{{ $messages->count() }}</span>
                    </p>
                </div>

                <div class="flex flex-col" style="height: 70vh;">

                    <div class="flex-1 overflow-y-auto p-5 space-y-3 bg-white">
                        @forelse($messages as $m)
                            @php
                                $isDonor = $m->sender_id === $conversation->donor_id;
                            @endphp

                            <div class="flex {{ $isDonor ? 'justify-start' : 'justify-end' }}">
                                <div
                                    class="max-w-[78%] rounded-2xl px-4 py-3 border
                            {{ $isDonor ? 'bg-gray-50 text-gray-800 border-gray-200' : 'bg-blue-600 text-white border-blue-600' }}">
                                    <div class="flex items-center justify-between gap-3 mb-1">
                                        <p class="text-xs font-semibold opacity-90">
                                            {{ $m->sender->name ?? 'User' }}
                                            <span class="opacity-70">•</span>
                                            <span class="opacity-70">{{ $isDonor ? 'Donor' : 'Recipient' }}</span>
                                        </p>
                                        <p class="text-[11px] opacity-80">
                                            {{ $m->created_at->format('Y-m-d H:i') }}
                                        </p>
                                    </div>

                                    <p class="text-sm whitespace-pre-line">{{ $m->body }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 text-sm py-16">
                                No messages in this conversation.
                            </div>
                        @endforelse
                    </div>

                    <div class="p-5 border-t bg-gray-50">
                        <div class="text-sm text-gray-600">
                            This is a <span class="font-semibold">read-only</span> view for admins.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>