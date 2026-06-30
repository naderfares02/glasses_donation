@php
    $lensTypes = [
        'single_vision' => 'Single Vision (Distance/Normal)',
        'bifocal' => 'Bifocal',
        'progressive' => 'Progressive',
        'reading' => 'Reading Glasses',
        'non_prescription' => 'Non-prescription / Fashion',
        'other' => 'Other',
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

    $visionTypes = [
        'distance' => 'Distance',
        'near' => 'Near (Reading)',
        'both' => 'Both',
        'unknown' => 'Unknown',
    ];

    $contactMethods = [
        'chat_only' => 'Chat inside platform',
        'phone' => 'Phone (if allowed)',
        'both' => 'Both',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add Glasses for Donation
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Fill in clear details to help recipients choose the right glasses.
                </p>
            </div>

            <a href="{{ route('donor.glasses.index') }}"
                class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to My Glasses
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Errors --}}
            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                    <p class="font-semibold text-sm">Please fix the following:</p>
                    <ul class="list-disc pl-5 mt-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('donor.glasses.store') }}" enctype="multipart/form-data"
                class="space-y-6">
                @csrf

                {{-- Top: Images --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Photos</p>
                        <p class="text-xs text-gray-500 mt-1">Add a clear main photo and up to 3 additional photos.</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Main Image --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Main Image *</label>

                            <div class="border rounded-2xl p-4 bg-gray-50">
                                <div id="mainPreviewBox"
                                    class="w-full h-64 rounded-xl bg-white border flex items-center justify-center overflow-hidden">
                                    <div class="text-sm text-gray-500">No image selected</div>
                                </div>

                                <input id="mainImageInput" type="file" name="main_image" accept="image/*" required
                                    class="mt-4 block w-full text-sm file:mr-4 file:py-2 file:px-4
                                              file:rounded-xl file:border-0 file:bg-blue-600 file:text-white
                                              hover:file:bg-blue-700">
                                <p class="text-xs text-gray-500 mt-2">Tip: Use a bright photo, front-facing.</p>
                            </div>
                        </div>

                        {{-- Additional Images --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Images (Max
                                3)</label>

                            <div class="border rounded-2xl p-4 bg-gray-50">
                                <div id="extraPreviewGrid" class="grid grid-cols-3 gap-3">
                                    <div
                                        class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                        —</div>
                                    <div
                                        class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                        —</div>
                                    <div
                                        class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                        —</div>
                                </div>

                                <input id="extraImagesInput" type="file" name="images[]" accept="image/*" multiple
                                    class="mt-4 block w-full text-sm file:mr-4 file:py-2 file:px-4
                                              file:rounded-xl file:border-0 file:bg-blue-600 file:text-white
                                              hover:file:bg-blue-700">
                                <p class="text-xs text-gray-500 mt-2">Optional: side view, close lens, case.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Basic info --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Basic Details</p>
                        <p class="text-xs text-gray-500 mt-1">Title + description + condition.</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                placeholder="Example: Ray-Ban frame, good condition"
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4"
                                placeholder="Any notes about scratches, comfort, accessories..."
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Condition *</label>
                            <select name="condition" required
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                <option value="new" @selected(old('condition') === 'new')>New</option>
                                <option value="used" @selected(old('condition') === 'used' || old('condition') === null)>
                                    Used</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Brand (optional)</label>
                            <input type="text" name="brand" value="{{ old('brand') }}"
                                placeholder="Example: Ray-Ban, Oakley..."
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>
                    </div>
                </div>

                {{-- Lens & Vision --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Lens & Vision</p>
                        <p class="text-xs text-gray-500 mt-1">Choose lens type and (optionally) enter prescription
                            details.</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Lens Type *</label>
                            <select name="lens_type" required
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($lensTypes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('lens_type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Vision Use</label>
                            <select name="vision_type"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($visionTypes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('vision_type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Prescription fields --}}
                        <div class="md:col-span-2">
                            <div class="border rounded-2xl p-4 bg-gray-50">
                                <p class="text-sm font-semibold text-gray-800">Prescription (optional)</p>
                                <p class="text-xs text-gray-500 mt-1">If you don’t know it, leave it empty.</p>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">SPH
                                            (Sphere)</label>
                                        <input name="sph" value="{{ old('sph') }}" placeholder="-1.50"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">CYL</label>
                                        <input name="cyl" value="{{ old('cyl') }}" placeholder="-0.75"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">AXIS</label>
                                        <input name="axis" value="{{ old('axis') }}" placeholder="180"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">PD</label>
                                        <input name="pd" value="{{ old('pd') }}" placeholder="62"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Prescription
                                        Note</label>
                                    <input name="prescription_note" value="{{ old('prescription_note') }}"
                                        placeholder="Example: Right eye only, reading +2.00..."
                                        class="w-full border rounded-xl px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fit & Style --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Fit & Style</p>
                        <p class="text-xs text-gray-500 mt-1">Optional details to help recipients pick the right fit.
                        </p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Frame Size</label>
                            <select name="frame_size"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($frameSizes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('frame_size') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Frame Color</label>
                            <input name="frame_color" value="{{ old('frame_color') }}"
                                placeholder="Black / Silver / Brown..."
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Age Group</label>
                            <select name="age_group"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($ageGroups as $key => $label)
                                    <option value="{{ $key }}" @selected(old('age_group') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                            <select name="gender"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($genders as $key => $label)
                                    <option value="{{ $key }}" @selected(old('gender') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Delivery --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Delivery</p>
                        <p class="text-xs text-gray-500 mt-1">Helps recipients know how to reach you and where to meet.
                        </p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pickup City / Area</label>
                            <input name="pickup_city" value="{{ old('pickup_city') }}"
                                placeholder="Example: Dortmund/stuttgart ..."
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>

                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-start gap-3 ">
                    <button type="submit"
                        class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-sm">
                        Save Glasses
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mainInput = document.getElementById('mainImageInput');
                const mainBox = document.getElementById('mainPreviewBox');

                const extraInput = document.getElementById('extraImagesInput');
                const extraGrid = document.getElementById('extraPreviewGrid');

                if (mainInput && mainBox) {
                    mainInput.addEventListener('change', (e) => {
                        const file = e.target.files?.[0];
                        if (!file) return;

                        const url = URL.createObjectURL(file);
                        mainBox.innerHTML = `<img src="${url}" class="w-full h-full object-cover" alt="preview">`;
                    });
                }

                if (extraInput && extraGrid) {
                    extraInput.addEventListener('change', (e) => {
                        const files = Array.from(e.target.files || []).slice(0, 3);
                        extraGrid.innerHTML = '';

                        for (let i = 0; i < 3; i++) {
                            const file = files[i];
                            if (!file) {
                                extraGrid.innerHTML += `<div class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">—</div>`;
                                continue;
                            }
                            const url = URL.createObjectURL(file);
                            extraGrid.innerHTML += `<div class="h-20 rounded-xl bg-white border overflow-hidden"><img src="${url}" class="w-full h-full object-cover" alt="extra"></div>`;
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>