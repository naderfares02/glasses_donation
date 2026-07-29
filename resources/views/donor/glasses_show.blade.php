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

    // عناوين جاهزة بدل القيم الخام
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

    $contactMethods = [
        'chat_only' => 'Chat only',
        'phone' => 'Phone',
        'both' => 'Both',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Glasses Details
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    View and manage this listing.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() }}"
                    class="px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                    ← Back
                </a>


            </div>
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

            {{-- Top: Hero --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-start gap-3 flex-wrap">
                            <h1 class="text-2xl font-extrabold text-gray-900 leading-snug break-words">
                                {{ $glasses->title }}
                            </h1>

                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $statusBadge }}">
                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                            </span>

                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $conditionBadge }}">
                                {{ strtoupper($glasses->condition) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-500 mt-2">
                            Added: <span
                                class="font-semibold text-gray-700">{{ $glasses->created_at?->format('Y-m-d') ?? '—' }}</span>
                            <span class="mx-2 text-gray-300">•</span>
                            Reference: <span class="font-semibold text-gray-700"> {{ $glasses->serial_number }}</span>
                        </p>
                    </div>
                    @if (!in_array($glasses->status, ['donated', 'pending_donation']))

                        {{-- Quick actions --}}
                        <div class="flex flex-wrap items-center gap-3">

                            {{-- View Contact Requests --}}
                            <a href="{{ route('donor.requests.index', ['glasses' => $glasses->id]) }}"
                                class="px-4 py-2.5 rounded-xl border bg-blue-50 hover:bg-blue-100 text-sm font-semibold text-blue-700">
                                Contact Requests
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('donor.glasses.edit', $glasses->id) }}"
                                class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">
                                Edit
                            </a>

                            {{-- Delete: available only --}}
                            @if ($glasses->status === 'available')
                                <form action="{{ route('donor.glasses.destroy', $glasses->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-4 py-2.5 rounded-xl border bg-red-50 hover:bg-red-100 text-sm font-semibold text-red-700">
                                        Delete
                                    </button>
                                </form>
                            @endif

                        </div>
                    @endif

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
                    {{-- Left: images --}}
                    <div class="lg:col-span-3 p-6 lg:border-r">
                        <div class="grid grid-cols-1 gap-4">
                            {{-- Main image --}}
                            <div class="rounded-2xl border bg-gray-50 overflow-hidden">
                                <div class="w-full h-[340px] bg-gray-100 flex items-center justify-center">
                                    @if($glasses->primaryImage)
                                        <img src="{{ asset('storage/' . $glasses->primaryImage->path) }}"
                                            class="w-full h-full object-cover" alt="Main">
                                    @else
                                        <div class="text-sm text-gray-500">No main image</div>
                                    @endif
                                </div>
                            </div>

                            {{-- thumbnails --}}
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

                    {{-- Right: status + key cards --}}
                    <div class="lg:col-span-2 p-6 space-y-4">
                        {{-- Status explainer --}}
                        <div class="rounded-2xl border bg-gray-50 p-5">
                            <p class="text-sm font-semibold text-gray-800">What does this status mean?</p>

                            <div class="mt-3 text-sm text-gray-700 leading-relaxed">
                                @if($status === 'available')
                                    <p>
                                        This item is visible to recipients and can receive contact requests.
                                    </p>
                                @elseif($status === 'reserved')
                                    <p>
                                        This item is <span class="font-semibold">reserved</span> for the recipient whose
                                        contact request you approved,
                                        to avoid conflicts with other recipients.
                                    </p>
                                @elseif($status === 'in_contact')
                                    <p>
                                        You are currently in contact with a recipient about this item.
                                    </p>
                                @elseif($status === 'pending_donation')
                                    <p>
                                        Donation is pending confirmation/approval according to your system rules.
                                    </p>
                                @elseif($status === 'donated')
                                    <p>
                                        Donation is completed.
                                    </p>
                                @else
                                    <p>
                                        Status is not recognized.
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Quick info --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-2xl border bg-white p-4">
                                <p class="text-xs text-gray-500">Lens Type</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ $lensTypes[$glasses->lens_type] ?? ($glasses->lens_type ?: '—') }}
                                </p>
                            </div>

                            <div class="rounded-2xl border bg-white p-4">
                                <p class="text-xs text-gray-500">Vision Use</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ $visionTypes[$glasses->vision_type] ?? ($glasses->vision_type ?: '—') }}
                                </p>
                            </div>

                            <div class="rounded-2xl border bg-white p-4">
                                <p class="text-xs text-gray-500">Pickup City</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ $glasses->pickup_city ?: '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border bg-white p-4">
                                <p class="text-xs text-gray-500">Contact Method</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ $contactMethods[$glasses->contact_method] ?? ($glasses->contact_method ?: '—') }}
                                </p>
                            </div>
                        </div>

                        {{-- Primary CTA (حسب الحالة) --}}
                        <div class="rounded-2xl border bg-white p-5">
                            <p class="text-sm font-semibold text-gray-800">Actions</p>

                            <div class="mt-4 space-y-2">
                                @if(in_array($status, ['available', 'reserved', 'in_contact']))
                                    {{-- زر: مثال فقط - عدّله حسب routes عندك --}}
                                    <a href="{{ route('donor.chats.index') }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold">
                                        Open Chats
                                    </a>

                                    {{-- مثال: mark donated (لو عندك route) --}}
                                    {{-- <a href="{{ route('donor.glasses.mark-donated.form', $glasses->id) }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                        Confirm Donation
                                    </a> --}}
                                @elseif($status === 'pending_donation')
                                    <div
                                        class="w-full px-4 py-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold text-center">
                                        Pending Donation Review
                                    </div>
                                @elseif($status === 'donated')
                                    <div
                                        class="w-full px-4 py-3 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm font-semibold text-center">
                                        Donation Completed
                                    </div>
                                @endif
                            </div>

                            <p class="text-xs text-gray-500 mt-3">
                                Tip: Keep communication inside the platform for safety.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom: details sections --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Description --}}
                <div class="lg:col-span-2 bg-white border rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Description</p>
                        <p class="text-xs text-gray-500 mt-1">Extra notes visible to recipients.</p>
                    </div>

                    <div class="p-6">
                        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                            {{ $glasses->description ?: 'No description provided.' }}
                        </div>
                    </div>
                </div>

                {{-- Fit & style --}}
                <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Fit & Style</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Brand</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $glasses->brand ?: '—' }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Frame Size</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $frameSizes[$glasses->frame_size] ?? ($glasses->frame_size ?: '—') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Frame Color</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $glasses->frame_color ?: '—' }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Age Group</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $ageGroups[$glasses->age_group] ?? ($glasses->age_group ?: '—') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Gender</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $genders[$glasses->gender] ?? ($glasses->gender ?: '—') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prescription (optional) --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50">
                    <p class="text-sm font-semibold text-gray-800">Prescription (optional)</p>
                    <p class="text-xs text-gray-500 mt-1">If you added prescription details, they appear here.</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="rounded-2xl border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">SPH</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $glasses->sph ?: '—' }}</p>
                        </div>

                        <div class="rounded-2xl border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">CYL</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $glasses->cyl ?: '—' }}</p>
                        </div>

                        <div class="rounded-2xl border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">AXIS</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $glasses->axis ?: '—' }}</p>
                        </div>

                        <div class="rounded-2xl border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">PD</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $glasses->pd ?: '—' }}</p>
                        </div>

                        <div class="rounded-2xl border bg-gray-50 p-4 lg:col-span-1 sm:col-span-2">
                            <p class="text-xs text-gray-500">Note</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $glasses->prescription_note ?: '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>