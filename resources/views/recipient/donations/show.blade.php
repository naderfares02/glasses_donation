<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Confirm Delivery</h2>
                <p class="text-sm text-gray-500 mt-1">Confirmation #{{ $confirmation->id }}</p>
            </div>

            <a href="{{ route('recipient.donations.index', ['tab' => $confirmation->status]) }}"
                class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back
            </a>
        </div>
    </x-slot>

    @php
        $dr = $confirmation->donationRequest;
        $g = $dr?->glasses;
        $donor = $dr?->donor;
    @endphp

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex items-start gap-5">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 border shrink-0">
                        @if($g?->primaryImage)
                            <img src="{{ asset('storage/' . $g->primaryImage->path) }}" class="w-full h-full object-cover"
                                alt="img">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-gray-500">No image</div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <p class="text-lg font-bold text-gray-800">{{ $g?->title ?? 'Glasses' }}</p>
                        <p class="text-sm text-gray-600 mt-1">
                            Donor: <span class="font-semibold">{{ $donor?->name ?? '—' }}</span>
                        </p>

                        <div class="mt-3">
                            <span class="text-xs font-semibold px-3 py-1 rounded-full border
                                {{ $confirmation->status === 'pending' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                {{ $confirmation->status === 'received' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                {{ $confirmation->status === 'not_received' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                            ">
                                {{ strtoupper($confirmation->status) }}
                            </span>
                        </div>

                        @if($confirmation->recipient_note)
                            <div class="mt-4 p-4 rounded-xl border
                                                            {{ $confirmation->status === 'received' ? 'bg-green-50 border-green-200 text-green-900' : '' }}
                                                            {{ $confirmation->status === 'not_received' ? 'bg-red-50 border-red-200 text-red-900' : '' }}
                                                            {{ $confirmation->status === 'pending' ? 'bg-gray-50 border-gray-200 text-gray-800' : '' }}
                                                        ">
                                <p class="text-xs font-semibold opacity-80 mb-1">Your note</p>
                                <p class="text-sm whitespace-pre-line">{{ $confirmation->recipient_note }}</p>
                            </div>
                        @endif

                        <div class="mt-4">
                            {{-- رابط صفحة النظارة --}}
                            @if($g)
                                <a href="{{ route('recipient.glasses.show', $g->id) }}"
                                    class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                                    View glasses details →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            @if($confirmation->status === 'pending')
                <div class="bg-white border rounded-2xl shadow-sm p-6">
                    <p class="text-sm font-semibold text-gray-800 mb-4">Your decision</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Received --}}
                        <form method="POST" action="{{ route('recipient.confirmations.received', $confirmation->id) }}"
                            class="border rounded-2xl p-4 bg-green-50 border-green-200">
                            @csrf
                            <p class="font-semibold text-green-900 mb-2">✅ I received it</p>
                            <textarea name="recipient_note" rows="3" class="w-full border rounded-xl px-3 py-2 text-sm"
                                placeholder="Optional note..."></textarea>

                            <button type="submit" onclick="return confirm('Confirm you received the donation?');"
                                class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl">
                                Confirm received
                            </button>
                        </form>

                        {{-- Not received --}}
                        <form method="POST" action="{{ route('recipient.confirmations.not_received', $confirmation->id) }}"
                            class="border rounded-2xl p-4 bg-red-50 border-red-200">
                            @csrf
                            <p class="font-semibold text-red-900 mb-2">❌ I did NOT receive it</p>
                            <textarea name="recipient_note" rows="3" required
                                class="w-full border rounded-xl px-3 py-2 text-sm"
                                placeholder="Please explain (required)"></textarea>

                            <button type="submit" onclick="return confirm('Submit NOT received?');"
                                class="mt-3 w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl">
                                Not received
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($confirmation && $confirmation->status === 'received')
                <div class="bg-white border rounded-2xl shadow-sm p-6">
                    <p class="text-sm font-semibold text-gray-800 mb-4">Delivery Status</p>

                    <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
                        You have confirmed that you received the glasses.
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>