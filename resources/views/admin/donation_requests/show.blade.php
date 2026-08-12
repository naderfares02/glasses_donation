<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Review Donation Request</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Request #{{ $donationRequest->id }}

                    {{-- Status badge --}}
                    @php
                        $status = $donationRequest->status;
                        $badge = match ($status) {
                            'pending' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                            'approved' => 'bg-green-50 text-green-700 border border-green-200',
                            'rejected' => 'bg-red-50 text-red-700 border border-red-200',
                            default => 'bg-gray-100 text-gray-700 border border-gray-200',
                        };
                    @endphp

                    <span
                        class="ml-2 inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full {{ $badge }}">
                        {{ strtoupper($status) }}
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if($donationRequest->conversation_id)
                    <a href="{{ route('admin.conversations.show', $donationRequest->conversation_id)}}"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-900 hover:bg-gray-800 text-white">
                        View Conversation
                    </a>
                @endif

                <a href="{{ route('admin.donation_requests.index') }}"
                    class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    ← Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex items-start gap-5">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 border shrink-0">
                        <a href="{{ route('admin.glasses.show', $donationRequest->glasses->id) }}">
                            @if($donationRequest->glasses?->primaryImage)
                                <img src="{{ asset('storage/' . $donationRequest->glasses->primaryImage->path) }}"
                                    class="w-full h-full object-cover" alt="img">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs text-gray-500">
                                    No image
                                </div>
                            @endif
                        </a>
                    </div>

                    <div class="flex-1">

                        <div class="flex items-start justify-between">
                            <p class="text-lg font-bold text-gray-800">
                                <a href="{{ route('admin.glasses.show', $donationRequest->glasses->id) }}">
                                    {{ $donationRequest->glasses->title ?? 'Glasses' }}
                                </a>
                            </p>
                            @if($donationRequest->receipt)
                                <a href="{{ route('admin.receipts.show', $donationRequest->receipt->id) }}"
                                    class="px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                    View Receipt
                                </a>
                            @endif
                        </div>


                        <p class="text-sm text-gray-600 mt-1">
                            Delivered date:
                            <span class="font-semibold">{{ $donationRequest->delivered_date ?? '—' }}</span>
                        </p>

                        <div class="mt-3 text-sm text-gray-700">
                            <p><span class="font-semibold">Donor:</span> <a
                                    href="{{ route('admin.users.show', $donationRequest->donor->id) }}">{{ $donationRequest->donor->name ?? '—' }}
                                </a> </p>
                            <p><span class="font-semibold">Recipient:</span>
                                <a href="{{ route('admin.users.show', $donationRequest->recipient->id) }}">
                                    {{ $donationRequest->recipient->name ?? '—' }}</a></ p>
                        </div>

                        @if($donationRequest->donor_note)
                            <div class="mt-4 p-4 bg-gray-50 border rounded-xl">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Donor note</p>
                                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $donationRequest->donor_note }}</p>
                            </div>
                        @endif


                        @php
                            $confirmation = $donationRequest->deliveryConfirmation;
                        @endphp

                        <div class="mt-4 p-4 rounded-xl border
    @if(!$confirmation || $confirmation->status === 'pending')
        bg-gray-50 border-gray-200
    @elseif($confirmation->status === 'received')
        bg-green-50 border-green-200
    @elseif($confirmation->status === 'not_received')
        bg-red-50 border-red-200
    @endif
">
                            <p class="text-xs font-semibold mb-1
        @if(!$confirmation || $confirmation->status === 'pending')
            text-gray-600
        @elseif($confirmation->status === 'received')
            text-green-700
        @elseif($confirmation->status === 'not_received')
            text-red-700
        @endif
    ">
                                Recipient confirmation
                            </p>

                            @if(!$confirmation || $confirmation->status === 'pending')
                                <p class="text-sm text-gray-700">
                                    Not confirmed yet.
                                </p>

                            @else
                                <p class="text-sm font-semibold
                                                    @if($confirmation->status === 'received')
                                                        text-green-800
                                                    @else
                                                        text-red-800
                                                    @endif
                                                ">
                                    @if($confirmation->status === 'received' && $confirmation->overridden_by)
                                        Marked as received (admin override)
                                    @elseif($confirmation->status === 'received')
                                        Confirmed by recipient
                                    @else
                                        Delivery denied by recipient
                                    @endif
                                </p>

                                @if($confirmation->recipient_note)
                                    <p
                                        class="text-sm mt-2
                                                                                                                                                                        @if($confirmation->status === 'received')
                                                                                                                                                                            text-green-900
                                                                                                                                                                        @else
                                                                                                                                                                            text-red-900
                                                                                                                                                                        @endif
                                                                                                                                                                    ">
                                        <span>Recipient note: </span> <span class="font-bold">
                                            {{ $confirmation->recipient_note }} </span>
                                    </p>
                                @endif
                            @endif
                        </div>

                        @if($confirmation && $confirmation->overridden_by)
                            <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-xl">
                                <p class="text-xs font-semibold text-purple-800 mb-1">
                                    Overridden by {{ $confirmation->overriddenBy?->name ?? 'Unknown admin' }}
                                    on {{ $confirmation->overridden_at?->format('Y-m-d H:i') }}
                                </p>
                                <p class="text-sm text-purple-900 whitespace-pre-line">{{ $confirmation->override_reason }}
                                </p>
                            </div>
                        @endif
                        @if($donationRequest->admin_note)
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                <p class="text-xs font-semibold text-blue-700 mb-1">Last admin note</p>
                                <p class="text-sm text-blue-900 whitespace-pre-line">{{ $donationRequest->admin_note }}</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <p class="text-sm font-semibold text-gray-800 mb-4">Decision</p>

                @if($confirmation && $confirmation->status === 'not_received')
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-xs font-semibold text-amber-800 mb-2">
                            Admin override (use only if you have verified the recipient actually received the item — e.g.
                            via a complaint thread)
                        </p>
                        <form method="POST"
                            action="{{ route('admin.donation_requests.override_confirmation', $donationRequest->id) }}">
                            @csrf
                            <textarea name="override_reason" rows="3" required minlength="10"
                                class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-200"
                                placeholder="Explain why you are overriding the recipient's denial (required, e.g. reference to complaint #, evidence shared, etc.)"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('override_reason')" />
                            <button type="submit"
                                onclick="return confirm('Override recipient denial and mark as received? This action is logged.');"
                                class="mt-2 px-4 py-2 rounded-xl text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white">
                                Override to "Received"
                            </button>
                        </form>
                    </div>
                @else

                    @if($donationRequest->status === 'approved')
                        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
                            This request is already approved.
                        </div>

                    @elseif ($donationRequest->status === 'rejected')
                        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
                            This request is already rejected.
                        </div>
                    @elseif ($donationRequest->status === 'pending')
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm">
                            No decision has been made yet.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.donation_requests.approve', $donationRequest->id) }}"
                        class="flex flex-col md:flex-row gap-3 md:items-end">
                        @csrf
                        <div class="flex-1 mt-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Admin note
                                ({{ $donationRequest->status === 'rejected' ? 'required to approve after rejection' : 'optional' }})
                            </label>

                            <textarea name="admin_note" rows="3" @if($donationRequest->status === 'rejected') required @endif
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-200"
                                placeholder="{{ $donationRequest->status === 'rejected' ? 'Explain why you are approving now...' : 'Notes for record...' }}"></textarea>
                        </div>

                        <button type="submit" onclick="return confirm('Approve this donation request?');"
                            class="px-5 py-3 rounded-xl text-sm font-semibold bg-green-600 hover:bg-green-700 text-white">
                            Approve
                        </button>
                    </form>

                    <div class="mt-4 border-t pt-4">
                        <form method="POST" action="{{ route('admin.donation_requests.reject', $donationRequest->id) }}"
                            class="flex flex-col md:flex-row gap-3 md:items-end">
                            @csrf

                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Rejection reason
                                    ({{ $donationRequest->status === 'approved' ? 'required to rejection after approve' : 'optional' }})
                                </label>
                                <textarea name="admin_note" rows="3" required
                                    class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-200"
                                    placeholder="Why rejected? (required)"></textarea>
                            </div>

                            <button type="submit" onclick="return confirm('Reject this donation request?');"
                                class="px-5 py-3 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                                Reject
                            </button>
                        </form>
                    </div>

                @endif

            </div>

        </div>
    </div>
</x-app-layout>