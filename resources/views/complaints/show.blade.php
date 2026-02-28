<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">
                    Complaint #{{ $complaint->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Against: {{ $complaint->reportedUser?->name }}
                </p>
            </div>

            {{-- Status Badge --}}
            @php
                $badge = match ($complaint->status) {
                    'open' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                    'reviewing' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'resolved' => 'bg-green-50 text-green-700 border-green-200',
                    'dismissed' => 'bg-gray-100 text-gray-600 border-gray-200',
                };
            @endphp

            <span class="px-4 py-2 rounded-full border text-sm font-semibold {{ $badge }}">
                {{ strtoupper($complaint->status) }}
            </span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
            {{-- Complaint Info --}}
            <div class="bg-white border rounded-2xl p-6 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500">Reason</p>
                        <p class="font-semibold text-gray-800 mt-1">
                            {{ ucfirst(str_replace('_', ' ', $complaint->reason)) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Glasses</p>
                        <p class="font-semibold text-gray-800 mt-1">
                            {{ $complaint->conversation?->glasses?->title ?? '—' }}
                        </p>
                    </div>
                </div>

                @if($complaint->description)
                    <div class="mt-6">
                        <p class="text-gray-500 text-sm">Initial Description</p>
                        <div class="mt-2 bg-gray-50 border rounded-xl p-4 text-sm text-gray-700 whitespace-pre-line">
                            {{ $complaint->description }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Messages --}}
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50">
                    <p class="text-sm font-semibold text-gray-700">Conversation</p>
                </div>

                <div class="p-6 space-y-4 max-h-[500px] overflow-y-auto bg-white">
                    @forelse($complaint->messages as $msg)
                        @php $mine = $msg->sender_id === auth()->id(); @endphp

                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            @if (auth()->user()->role === 'recipient')
                                <div
                                    class="max-w-[75%] rounded-2xl px-4 py-3
                                                                                                {{ $mine ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                            @else
                                    <div
                                        class="max-w-[75%] rounded-2xl px-4 py-3
                                                                                                {{ $mine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                @endif
                                    <p class="text-sm whitespace-pre-line">{{ $msg->body }}</p>
                                    <p class="text-[11px] mt-2 opacity-75">
                                        {{ $msg->sender->name }} • {{ $msg->created_at->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                            </div>
                    @empty
                            <p class="text-center text-gray-500 text-sm">No messages yet.</p>
                        @endforelse
                    </div>

                    {{-- Send Message --}}
                    @if(!in_array($complaint->status, ['resolved', 'dismissed']))
                        <div class="p-5 border-t bg-gray-50">
                            <form method="POST" action="{{ route('complaints.message', $complaint->id) }}"
                                class="flex gap-3">
                                @csrf
                                <textarea name="body" rows="2" required
                                    class="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-200"
                                    placeholder="Write a message..."></textarea>

                                @if (auth()->user()->role === 'recipient')
                                    <button type="submit"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 rounded-xl">Send</button>
                                @else
                                    <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 rounded-xl">
                                        Send
                                @endif

                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-5 text-sm text-gray-500 text-center border-t bg-gray-50">
                            This complaint is closed.
                        </div>
                    @endif
                </div>



            </div>
        </div>
</x-app-layout>