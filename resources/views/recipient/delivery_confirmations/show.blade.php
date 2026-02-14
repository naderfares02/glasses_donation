<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Confirm Delivery</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Please confirm if you received the donation.
                </p>
            </div>

            <a href="{{ route('recipient.main_page') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Card: Glasses info --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex items-start gap-5">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 border shrink-0">
                        @if($confirmation->glasses?->primaryImage)
                            <img src="{{ asset('storage/' . $confirmation->glasses->primaryImage->path) }}"
                                 class="w-full h-full object-cover" alt="img">
                        @endif
                    </div>

                    <div class="flex-1">
                        <p class="text-lg font-bold text-gray-800">
                            {{ $confirmation->glasses->title ?? 'Glasses' }}
                        </p>

                        <p class="text-sm text-gray-600 mt-1">
                            Donor: <span class="font-semibold">{{ $confirmation->donor->name ?? '—' }}</span>
                        </p>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('recipient.glasses.show', $confirmation->glasses_id) }}"
                               class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                View Glasses
                            </a>

                            <span class="text-xs font-semibold px-3 py-2 rounded-xl border
                                {{ $confirmation->status === 'pending' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : '' }}
                                {{ $confirmation->status === 'received' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                {{ $confirmation->status === 'not_received' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                            ">
                                Status: {{ strtoupper($confirmation->status) }}
                            </span>
                        </div>

                        @if($confirmation->donor_note)
                            <div class="mt-5 p-4 bg-gray-50 border rounded-xl">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Donor note</p>
                                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $confirmation->donor_note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <p class="text-sm font-semibold text-gray-800 mb-4">Your response</p>

                @if($confirmation->status !== 'pending')
                    <div class="p-4 bg-gray-50 border rounded-xl">
                        <p class="text-sm text-gray-700">
                            You already responded on:
                            <span class="font-semibold">{{ optional($confirmation->recipient_responded_at)->format('Y-m-d H:i') ?? '—' }}</span>
                        </p>

                        @if($confirmation->recipient_note)
                            <div class="mt-3">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Your note</p>
                                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $confirmation->recipient_note }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Confirm received --}}
                    <form method="POST" action="{{ route('recipient.delivery_confirmations.confirm', $confirmation->id) }}"
                          class="border rounded-2xl p-5 bg-green-50/40">
                        @csrf
                        <p class="text-sm font-semibold text-green-800">✅ I received the glasses</p>

                        <label class="block text-sm font-semibold text-gray-700 mt-3 mb-2">
                            Note (optional)
                        </label>
                        <textarea name="recipient_note" rows="3"
                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-200"
                            placeholder="Any note for admin/donor..."></textarea>

                        <button type="submit"
                            onclick="return confirm('Confirm that you received the glasses?');"
                            class="mt-4 px-5 py-3 rounded-xl text-sm font-semibold bg-green-600 hover:bg-green-700 text-white">
                            Confirm received
                        </button>
                    </form>

                    {{-- Deny received --}}
                    <form method="POST" action="{{ route('recipient.delivery_confirmations.deny', $confirmation->id) }}"
                          class="border rounded-2xl p-5 bg-red-50/40 mt-5">
                        @csrf
                        <p class="text-sm font-semibold text-red-800">❌ I did NOT receive the glasses</p>

                        <label class="block text-sm font-semibold text-gray-700 mt-3 mb-2">
                            Explain why (required)
                        </label>
                        <textarea name="recipient_note" rows="3" required
                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-200"
                            placeholder="Please write details... (required)"></textarea>

                        <button type="submit"
                            onclick="return confirm('Submit "not received" response?');"
                            class="mt-4 px-5 py-3 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                            Submit not received
                        </button>
                    </form>

                    @error('recipient_note')
                        <p class="text-sm text-red-600 mt-3">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        </div>
    </div>
</x-app-layout>