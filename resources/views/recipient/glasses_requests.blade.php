<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">My Requests</h2>
                <p class="text-sm text-gray-500 mt-1">
                    List of glasses you have requested.
                </p>
            </div>
        </div>
    </x-slot>

    <div>
        @if($requests->isEmpty())
            <div class="p-12 text-center text-gray-600">
                <p class="font-semibold">You haven't submitted any requests yet.</p>
                <p class="text-sm text-gray-500 mt-2">
                    Your requests will appear here after you contact a donor.
                </p>
            </div>
        @else
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($requests as $request)

                    @php
                        $badge = match ($request->status) {
                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'accepted' => 'bg-green-50 text-green-700 border-green-200',
                            'rejected' => 'bg-red-50 text-red-700 border-red-200',
                            'closed' => 'bg-gray-50 text-gray-700 border-gray-200',
                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                        };

                        $isDonatedToMe = in_array($request->glasses_id, $donatedGlassesIds);
                    @endphp

                    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">

                        {{-- Header --}}
                        <div class="p-5 border-b bg-gray-50 flex items-start justify-between">

                            <div>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $request->donor->name }}
                                </p>


                                @if($request->glasses)
                                    <p class="text-xs text-gray-500 mt-2">
                                        Reference:
                                        <span class="font-semibold text-gray-700">
                                            {{ $request->glasses->serial_number }}
                                        </span>
                                    </p>
                                @endif
                            </div>


                            <div class="flex flex-row items-center gap-2">

                                @if($isDonatedToMe)
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full border text-xs font-semibold bg-green-100 text-green-800 border-green-300">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Donated to you
                                    </span>
                                @endif

                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $badge }}">
                                    {{ ucfirst($request->status) }}
                                </span>

                            </div>

                        </div>

                        {{-- Body --}}
                        <div class="p-5 space-y-3">

                            @if($request->glasses)
                                <div>
                                    <p class="text-xs text-gray-500">Glasses</p>
                                    <p class="font-medium text-gray-900">
                                        <a href="{{ route('recipient.glasses.show', $request->glasses->id) }}">
                                            {{ $request->glasses->title ?? ('#' . $request->glasses->id)}} </a>
                                    </p>
                                </div>
                            @endif

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Requested at</span>
                                <span class="font-medium text-gray-900">
                                    {{ $request->created_at->format('Y-m-d H:i') }}
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="pt-3">

                                @if($request->status === 'accepted')

                                    <a href="{{ route('recipient.chats.index', ['conversation' => $request->conversation]) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                                        Message Donor
                                    </a>

                                @elseif($request->status === 'pending')

                                    <div class="flex items-center justify-between w-full">
                                        <span class="text-sm text-yellow-700">
                                            Waiting for donor response.
                                        </span>

                                        <form action="{{ route('recipient.requests.withdraw', $request->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to withdraw this request?');">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="px-3 py-1 rounded-lg bg-red-50 text-red-600 border border-red-200 text-sm font-semibold hover:bg-red-100">
                                                Withdraw
                                            </button>
                                        </form>
                                    </div>



                                @elseif($request->status === 'on_hold')

                                    <span class="text-sm text-yellow-700">
                                        Waiting for donor response.
                                    </span>

                                @elseif($request->status === 'rejected')

                                    <span class="text-sm text-red-600">
                                        This request was rejected.
                                    </span>

                                @elseif($request->status === 'closed')

                                    <span class="text-sm text-gray-500">
                                        This request has been closed.
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>