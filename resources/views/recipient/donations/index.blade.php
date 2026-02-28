<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Donations</h2>
                <p class="text-sm text-gray-500 mt-1">Confirm delivery status of donations.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tabs --}}
            <div class="bg-white border rounded-2xl shadow-sm p-4">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('recipient.donations.index', ['tab' => 'pending']) }}"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border
                       {{ $tab === 'pending' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                        Pending
                        <span
                            class="ml-2 text-xs px-2 py-0.5 rounded-full {{ $tab === 'pending' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ (int) ($counts->pending_count ?? 0) }}
                        </span>
                    </a>

                    <a href="{{ route('recipient.donations.index', ['tab' => 'received']) }}"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border
                       {{ $tab === 'received' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                        Received
                        <span
                            class="ml-2 text-xs px-2 py-0.5 rounded-full {{ $tab === 'received' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ (int) ($counts->received_count ?? 0) }}
                        </span>
                    </a>

                    <a href="{{ route('recipient.donations.index', ['tab' => 'not_received']) }}"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border
                       {{ $tab === 'not_received' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                        Not received
                        <span
                            class="ml-2 text-xs px-2 py-0.5 rounded-full {{ $tab === 'not_received' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ (int) ($counts->not_received_count ?? 0) }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($confirmations as $c)
                    @php
                        $dr = $c->donationRequest;
                        $g = $dr?->glasses;
                        $donor = $dr?->donor;
                        $drStatus = $c->donationRequest?->status; // pending / approved / rejected ...
                    @endphp

                    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">
                        <div class="h-44 bg-gray-100">
                            @if($g?->primaryImage)
                                <img src="{{ asset('storage/' . $g->primaryImage->path) }}" class="w-full h-full object-cover"
                                    alt="img">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-500 text-sm">
                                    No image
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-800 truncate">{{ $g?->title ?? 'Glasses' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Donor: <span class="font-semibold">{{ $donor?->name ?? '—' }}</span>
                                    </p>
                                </div>

                                {{-- status badge --}}
                                <span class="text-xs font-semibold px-3 py-1 rounded-full border
                                                                {{ $c->status === 'pending' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                                                {{ $c->status === 'received' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                                                {{ $c->status === 'not_received' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                                            ">
                                    {{ strtoupper($c->status) }}
                                </span>
                            </div>

                            @if($c->recipient_note)

                                <div class="mt-3 p-4 bg-green-50 border rounded-xl">
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Your note</p>
                                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $c->recipient_note }}</p>
                                </div>



                            @elseif($c->status === 'pending')
                                <div class="mt-4 p-3 rounded-xl border bg-blue-50 border-blue-200 flex items-start gap-2">
                                    <span class="text-blue-600 text-lg leading-none">⏳</span>
                                    <div>
                                        <p class="text-sm font-semibold text-blue-800">
                                            Confirmation needed
                                        </p>
                                        <p class="text-xs text-blue-700">
                                            You haven’t confirmed receiving this donation yet.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-5 flex items-center justify-between">

                                @if($c->status === 'received')

                                    <a href="{{ route('recipient.glasses.show', $g->id)}}"
                                        class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                                        View details →
                                    </a>

                                    @if($tab === 'received' && $drStatus === 'approved')
                                        <div
                                            class="mt-3 inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full
                                                                                    bg-green-50 text-green-700 border border-green-200">
                                            ✅ Approved by admin
                                        </div>
                                    @elseif($tab === 'received' && $drStatus === 'pending')
                                        <div class="mt-3 inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full
                                                                                    border bg-amber-50 border-amber-200">
                                            ⚠️ Reviewing by admin
                                        </div>
                                    @endif

                                @endif

                                @if($c->status === 'pending')
                                    <span class="text-xs text-gray-500">Action required</span>
                                    <a href="{{ route('recipient.confirmations.show', $c->id)}}"
                                        class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                                        Confirm →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 sm:col-span-2 lg:col-span-3">
                        <div class="p-10 bg-white border rounded-2xl text-center text-gray-600">
                            No items in this tab.
                        </div>
                    </div>
                @endforelse
            </div>

            <div>
                {{ $confirmations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>