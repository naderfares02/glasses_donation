<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Conversation</h2>
                <p class="text-sm text-gray-500">
                    Glasses: <span class="font-semibold">{{ $conversation->glasses->title }}</span>
                </p>
            </div>

            {{-- رجوع حسب الدور --}}
            @if(auth()->user()->role === 'donor')
                <a href="{{ route('donor.requests.index', $conversation->glasses_id) }}"
                    class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    ← Back to Requests
                </a>
            @else
                <a href="{{ route('recipient.glasses.show', $conversation->glasses_id) }}"
                    class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    ← Back
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                {{-- Header inside card --}}
                <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        @php
                            $other = auth()->id() === $conversation->donor_id
                                ? $conversation->recipient
                                : $conversation->donor;
                        @endphp
                        Chat with: <span class="font-semibold">{{ $other->name }}</span>
                    </div>

                    <span
                        class="text-xs font-semibold px-3 py-1 rounded-full
                        {{ $conversation->status === 'open' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                        {{ strtoupper($conversation->status) }}
                    </span>
                </div>

                {{-- Messages --}}
                <div class="p-5 h-[420px] overflow-y-auto space-y-3 bg-white">
                    @forelse($messages as $m)
                        @php $mine = $m->sender_id === auth()->id(); @endphp

                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%] rounded-2xl px-4 py-3
                                                {{ $mine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                <p class="text-sm whitespace-pre-line">{{ $m->body }}</p>
                                <p class="text-[11px] mt-2 opacity-75">
                                    {{ $m->created_at->format('Y-m-d H:i') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 text-sm py-10">
                            No messages yet. Start the conversation.
                        </div>
                    @endforelse
                </div>

                {{-- Send message --}}
                <div class="p-5 border-t bg-gray-50">
                    @if($conversation->status === 'open')
                        <form wire:submit.prevent="send">
                            <div class="flex gap-3">
                                <textarea wire:model.live="body" rows="2"
                                    class="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-200"
                                    placeholder="Write a message..."></textarea>

                                <button type="submit"
                                    class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 rounded-xl">
                                    Send
                                </button>
                            </div>

                            @error('body')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </form>
                    @else
                        <p class="text-sm text-gray-600">This conversation is closed.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>