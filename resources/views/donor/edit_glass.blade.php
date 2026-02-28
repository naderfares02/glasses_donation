@php
    $lensTypes = [
        'single_vision' => 'Single Vision (Distance/Normal)',
        'bifocal' => 'Bifocal',
        'progressive' => 'Progressive',
        'reading' => 'Reading Glasses',
        'non_prescription' => 'Non-prescription / Fashion',
        'other' => 'Other',
    ];

    $frameSizes = ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'unknown' => 'Unknown'];
    $ageGroups = ['adult' => 'Adult', 'kids' => 'Kids', 'teen' => 'Teen', 'unknown' => 'Unknown'];
    $genders = ['male' => 'Male', 'female' => 'Female', 'unisex' => 'Unisex', 'unknown' => 'Unknown'];
    $visionTypes = ['distance' => 'Distance', 'near' => 'Near (Reading)', 'both' => 'Both', 'unknown' => 'Unknown'];
    $contactMethods = ['chat_only' => 'Chat inside platform', 'phone' => 'Phone (if allowed)', 'both' => 'Both'];

    $extras = $glasses->images?->where('is_primary', false) ?? collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Glasses</h2>
                <p class="text-sm text-gray-500 mt-1">Update details and photos.</p>
            </div>

            <a href="{{ route('donor.glasses.index') }}"
                class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to My Glasses
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                    <p class="font-semibold text-sm">Success</p>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            @endif

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

            <form method="POST" action="{{ route('donor.glasses.update', $glasses->id) }}" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @method('PATCH')

                {{-- Photos --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Photos</p>
                        <p class="text-xs text-gray-500 mt-1">You can replace main image and add more images (max 3
                            extras).</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Current main + replace --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Main Image</label>

                            <div class="border rounded-2xl p-4 bg-gray-50">
                                <div id="mainPreviewBox"
                                    class="w-full h-64 rounded-xl bg-white border flex items-center justify-center overflow-hidden">
                                    @if($glasses->primaryImage)
                                        <img src="{{ asset('storage/' . $glasses->primaryImage->path) }}"
                                            class="w-full h-full object-cover" alt="Main">
                                    @else
                                        <div class="text-sm text-gray-500">No main image</div>
                                    @endif
                                </div>

                                <input id="mainImageInput" type="file" name="main_image" accept="image/*" class="mt-4 block w-full text-sm file:mr-4 file:py-2 file:px-4
                                              file:rounded-xl file:border-0 file:bg-blue-600 file:text-white
                                              hover:file:bg-blue-700">
                                <p class="text-xs text-gray-500 mt-2">Uploading a new file will replace the current main
                                    image.</p>
                            </div>
                        </div>

                        {{-- Current extras + add new --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Images</label>

                            <div class="border rounded-2xl p-4 bg-gray-50">
                                {{-- Current --}}
                                <div class="grid grid-cols-3 gap-3">
                                    @forelse($extras as $img)
                                        <a href="{{ asset('storage/' . $img->path) }}" target="_blank"
                                            class="h-20 rounded-xl bg-white border overflow-hidden block">
                                            <img src="{{ asset('storage/' . $img->path) }}"
                                                class="w-full h-full object-cover" alt="Extra">
                                        </a>
                                    @empty
                                        <div
                                            class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                            —</div>
                                        <div
                                            class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                            —</div>
                                        <div
                                            class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                            —</div>
                                    @endforelse
                                </div>

                                {{-- New previews --}}
                                <div id="extraPreviewGrid" class="grid grid-cols-3 gap-3 mt-4">
                                    <div
                                        class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                        New</div>
                                    <div
                                        class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                        New</div>
                                    <div
                                        class="h-20 rounded-xl bg-white border flex items-center justify-center text-xs text-gray-500">
                                        New</div>
                                </div>

                                <input id="extraImagesInput" type="file" name="images[]" accept="image/*" multiple
                                    class="mt-4 block w-full text-sm file:mr-4 file:py-2 file:px-4
                                              file:rounded-xl file:border-0 file:bg-gray-800 file:text-white
                                              hover:file:bg-gray-900">
                                <p class="text-xs text-gray-500 mt-2">Only the first allowed images will be saved (max 3
                                    extras total).</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Basic Details --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Basic Details</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" value="{{ old('title', $glasses->title) }}" required
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4"
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">{{ old('description', $glasses->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Condition *</label>
                            <select name="condition" required
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                <option value="new" @selected(old('condition', $glasses->condition) === 'new')>New
                                </option>
                                <option value="used" @selected(old('condition', $glasses->condition) === 'used')>Used
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Brand</label>
                            <input name="brand" value="{{ old('brand', $glasses->brand) }}"
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>
                    </div>
                </div>

                {{-- Lens & Vision --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Lens & Vision</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Lens Type *</label>
                            <select name="lens_type" required
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($lensTypes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('lens_type', $glasses->lens_type) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Vision Use</label>
                            <select name="vision_type"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($visionTypes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('vision_type', $glasses->vision_type) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <div class="border rounded-2xl p-4 bg-gray-50">
                                <p class="text-sm font-semibold text-gray-800">Prescription (optional)</p>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">SPH</label>
                                        <input name="sph" value="{{ old('sph', $glasses->sph) }}"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">CYL</label>
                                        <input name="cyl" value="{{ old('cyl', $glasses->cyl) }}"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">AXIS</label>
                                        <input name="axis" value="{{ old('axis', $glasses->axis) }}"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">PD</label>
                                        <input name="pd" value="{{ old('pd', $glasses->pd) }}"
                                            class="w-full border rounded-xl px-3 py-2 text-sm">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Prescription
                                        Note</label>
                                    <input name="prescription_note"
                                        value="{{ old('prescription_note', $glasses->prescription_note) }}"
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
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Frame Size</label>
                            <select name="frame_size"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($frameSizes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('frame_size', $glasses->frame_size) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Frame Color</label>
                            <input name="frame_color" value="{{ old('frame_color', $glasses->frame_color) }}"
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Age Group</label>
                            <select name="age_group"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($ageGroups as $key => $label)
                                    <option value="{{ $key }}" @selected(old('age_group', $glasses->age_group) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                            <select name="gender"
                                class="w-full border rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                @foreach($genders as $key => $label)
                                    <option value="{{ $key }}" @selected(old('gender', $glasses->gender) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Delivery & Contact --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Delivery</p>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pickup City / Area</label>
                            <input name="pickup_city" value="{{ old('pickup_city', $glasses->pickup_city) }}"
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                        </div>


                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('donor.glasses.index') }}"
                        class="px-5 py-3 rounded-2xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm">
                        Save Changes
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