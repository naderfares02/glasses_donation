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

    $conditionBadge = ($glasses->condition === 'new')
        ? 'bg-green-50 text-green-700 border-green-200'
        : 'bg-gray-50 text-gray-700 border-gray-200';

    $extras = $glasses->images?->where('is_primary', false) ?? collect();

    $val = fn($v) => ($v === null || $v === '') ? null : $v;

    // Titles
    $lensTypes = [
        'single_vision' => 'Single Vision',
        'bifocal' => 'Bifocal',
        'progressive' => 'Progressive',
        'reading' => 'Reading',
        'non_prescription' => 'Non-prescription',
        'other' => 'Other',
    ];

    $visionTypes = [
        'distance' => 'Distance',
        'near' => 'Near (Reading)',
        'both' => 'Both',
        'unknown' => 'Unknown',
    ];

    $frameSizes = [
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
        'unknown' => 'Unknown',
    ];

    $ageGroups = [
        'adult' => 'Adult',
        'kids' => 'Kids',
        'teen' => 'Teen',
        'unknown' => 'Unknown',
    ];

    $genders = [
        'male' => 'Male',
        'female' => 'Female',
        'unisex' => 'Unisex',
        'unknown' => 'Unknown',
    ];

    $quickFacts = collect([
        ['label' => 'Lens Type', 'value' => $lensTypes[$glasses->lens_type] ?? $val($glasses->lens_type)],
        ['label' => 'Vision Use', 'value' => $visionTypes[$glasses->vision_type] ?? $val($glasses->vision_type)],
        ['label' => 'Pickup City', 'value' => $val($glasses->pickup_city)],
        ['label' => 'Brand', 'value' => $val($glasses->brand)],
        ['label' => 'Frame Size', 'value' => $frameSizes[$glasses->frame_size] ?? $val($glasses->frame_size)],
        ['label' => 'Frame Color', 'value' => $val($glasses->frame_color)],
        ['label' => 'Age Group', 'value' => $ageGroups[$glasses->age_group] ?? $val($glasses->age_group)],
        ['label' => 'Gender', 'value' => $genders[$glasses->gender] ?? $val($glasses->gender)],
    ])->filter(fn($x) => $x['value'] !== null)->values();

    $prescriptionFields = collect([
        ['label' => 'SPH', 'value' => $val($glasses->sph)],
        ['label' => 'CYL', 'value' => $val($glasses->cyl)],
        ['label' => 'AXIS', 'value' => $val($glasses->axis)],
        ['label' => 'PD', 'value' => $val($glasses->pd)],
        ['label' => 'Note', 'value' => $val($glasses->prescription_note)],
    ])->filter(fn($x) => $x['value'] !== null)->values();

    $hasDescription = (bool) $val($glasses->description);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Glasses Details
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Check details and request contact with the donor.
                </p>
            </div>

            <a href="{{ route('recipient.main_page') }}"
                class="px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Main content --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- Hero card --}}
                    <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">

                        <div class="p-6 border-b bg-gray-50">

                            <div class="flex items-start justify-between gap-6">

                                <div class="min-w-0">

                                    <h1 class="text-2xl font-extrabold text-gray-900 leading-snug break-words">
                                        {{ $glasses->title }}
                                    </h1>

                                    <p class="text-sm text-gray-600 mt-2">
                                        Donated by:
                                        <span class="font-semibold text-gray-800">
                                            @auth
                                                {{ $glasses->user->name }}
                                            @else
                                                A verified donor
                                            @endauth
                                        </span>
                                    </p>

                                    <p class="text-sm text-gray-500 mt-2">
                                        Added:
                                        <span class="font-semibold text-gray-700">
                                            {{ $glasses->created_at?->format('Y-m-d') ?? '—' }}
                                        </span>

                                        <span class="mx-2 text-gray-300">•</span>

                                        Reference:
                                        <span class="font-semibold text-gray-700">
                                            {{ $glasses->serial_number }}
                                        </span>
                                    </p>

                                    <p class="text-sm text-gray-500 mt-1">
                                        Condition:
                                        <span class="font-semibold text-gray-700">
                                            {{ strtoupper($glasses->condition) }}
                                        </span>
                                    </p>

                                </div>


                                {{-- System Status --}}
                                <div class="shrink-0">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $statusBadge }}">
                                        {{ strtoupper(str_replace('_', ' ', $status)) }}
                                    </span>
                                </div>

                            </div>

                        </div>

                        {{-- Images --}}
                        <div class="p-6 space-y-4">
                            <div class="rounded-2xl border bg-gray-50 overflow-hidden">
                                <div class="w-full h-[360px] bg-gray-100 flex items-center justify-center">
                                    @if($glasses->primaryImage)
                                        <img src="{{ asset('storage/' . $glasses->primaryImage->path) }}"
                                            class="w-full h-full object-cover" alt="Main">
                                    @else
                                        <div class="text-sm text-gray-500">No image available</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Show thumbnails only if exist --}}
                            @if($extras->count())
                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                                    @foreach($extras as $img)
                                        <a href="{{ asset('storage/' . $img->path) }}" target="_blank"
                                            class="h-16 rounded-xl border bg-white overflow-hidden hover:shadow-sm transition">
                                            <img src="{{ asset('storage/' . $img->path) }}" class="w-full h-full object-cover"
                                                alt="Extra">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Facts (only if there are facts) --}}
                    @if($quickFacts->count())
                        <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                            <div class="p-5 border-b bg-gray-50">
                                <p class="text-sm font-semibold text-gray-800">Details</p>
                                <p class="text-xs text-gray-500 mt-1">Useful info to help you decide.</p>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($quickFacts as $fact)
                                        <div class="rounded-2xl border bg-white p-4">
                                            <p class="text-xs text-gray-500">{{ $fact['label'] }}</p>
                                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                                {{ $fact['value'] }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Description only if exists --}}
                    @if($hasDescription)
                        <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                            <div class="p-5 border-b bg-gray-50">
                                <p class="text-sm font-semibold text-gray-800">Description</p>
                            </div>

                            <div class="pl-6 pb-6">
                                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                                    {{ $glasses->description }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Prescription only if any field exists --}}
                    @if($prescriptionFields->count())
                        <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                            <div class="p-5 border-b bg-gray-50">
                                <p class="text-sm font-semibold text-gray-800">Prescription</p>
                                <p class="text-xs text-gray-500 mt-1">Only if donor provided it.</p>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                    @foreach($prescriptionFields as $pf)
                                        <div
                                            class="rounded-2xl border bg-gray-50 p-4 {{ $pf['label'] === 'Note' ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                                            <p class="text-xs text-gray-500">{{ $pf['label'] }}</p>
                                            <p class="text-sm font-semibold text-gray-900 mt-1 whitespace-pre-line">
                                                {{ $pf['value'] }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar CTA --}}
                <div class="lg:col-span-4 space-y-6">

                    {{-- Contact / action card (never empty) --}}
                    <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                        <div class="p-5 border-b bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Get this item</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Send a contact request to the donor.
                            </p>
                        </div>

                        <div class="p-6 space-y-4">
                            @if($status === 'available')
                                @auth
                                    <form method="POST" action="{{ route('recipient.contact-requests.store', $glasses->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                                            Request Contact
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                                        Login to Request This Item
                                    </a>
                                @endauth

                                <div class="rounded-2xl border bg-blue-50 border-blue-200 p-4">
                                    <p class="text-sm font-semibold text-blue-800">What happens next?</p>
                                    <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                                        When the donor approves your contact request, the glasses will be reserved for you
                                        to avoid conflicts with other recipients.
                                    </p>
                                </div>

                            @elseif($status === 'reserved')
                                <div class="rounded-2xl border bg-purple-50 border-purple-200 p-4">
                                    <p class="text-sm font-semibold text-purple-800">Reserved</p>
                                    <p class="text-xs text-purple-700 mt-1 leading-relaxed">
                                        This listing is currently reserved for the recipient whose contact request was
                                        approved.
                                    </p>
                                </div>

                                <a href="{{ route('recipient.main_page') }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                    Browse other items
                                </a>

                            @elseif(in_array($status, ['in_contact', 'pending_donation'], true))
                                <div class="rounded-2xl border bg-gray-50 p-4">
                                    <p class="text-sm font-semibold text-gray-800">Not available right now</p>
                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                        This item is currently in progress or completed. You can browse other available
                                        listings.
                                    </p>
                                </div>

                                <a href="{{ route('recipient.main_page') }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold">
                                    View available glasses
                                </a>
                            @elseif($status === 'donated')
                                <div class="rounded-2xl border bg-green-50 border-green-200 p-4">
                                    <p class="text-sm font-semibold text-green-800">Donation Completed</p>
                                    <p class="text-xs text-green-700 mt-1 leading-relaxed">
                                        This item has been marked as donated.
                                    </p>
                                </div>

                                @if($glasses->receipt)
                                    <a href="{{ route('recipient.receipts.show', $glasses->receipt->id) }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                        View Receipt
                                    </a>
                                @endif

                                <a href="{{ route('recipient.main_page') }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                    View available glasses
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Safety/Guidelines (never empty) --}}
                    <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                        <div class="p-5 border-b bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Tips</p>
                            <p class="text-xs text-gray-500 mt-1">Make the donation smooth.</p>
                        </div>

                        <div class="p-6 space-y-3 text-sm text-gray-700">
                            <div class="flex gap-3">
                                <span class="mt-0.5 text-gray-400">•</span>
                                <p>Be clear about pickup location and time.</p>
                            </div>
                            <div class="flex gap-3">
                                <span class="mt-0.5 text-gray-400">•</span>
                                <p>Confirm lens type and prescription details if needed.</p>
                            </div>
                            <div class="flex gap-3">
                                <span class="mt-0.5 text-gray-400">•</span>
                                <p>Once approved, the item becomes reserved to prevent conflicts.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>