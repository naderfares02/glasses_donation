<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Glasses
        </h2>
    </x-slot>

    <div class="p-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif


            @foreach($glasses->images->where('is_primary', false) as $img)
                <form id="delete-image-{{ $img->id }}"
                    action="{{ route('donor.glasses.images.destroy', [$glasses->id, $img->id]) }}" method="POST"
                    class="hidden" onsubmit="return confirm('Delete this image?');">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach


            <div class="bg-white p-5 rounded-lg shadow-md">
                <form method="POST" action="{{ route('donor.glasses.update', $glasses->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Title</label>
                        <input type="text" name="title" value="{{ old('title', $glasses->title) }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm">{{ old('description', $glasses->description) }}</textarea>
                    </div>

                    <!-- Lens Type -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Lens Type</label>
                        <input type="text" name="lens_type" value="{{ old('lens_type', $glasses->lens_type) }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm">
                    </div>

                    <!-- Prescription -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Prescription</label>
                        <input type="text" name="prescription" value="{{ old('prescription', $glasses->prescription) }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm">
                    </div>

                    <!-- Condition -->
                    <div class="mb-6">
                        <label class="block font-medium mb-1">Condition</label>
                        <select name="condition" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                            <option value="new" @selected(old('condition', $glasses->condition) === 'new')>New</option>
                            <option value="used" @selected(old('condition', $glasses->condition) === 'used')>Used</option>
                        </select>
                    </div>

                    {{-- Images Section --}}
                    <div class="mt-10 border-t pt-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">Images</h3>

                        {{-- Main Image --}}
                        <div class="bg-white border rounded-xl p-6 shadow-sm mb-8">
                            <p class="text-sm font-semibold text-gray-700 mb-4">Main Image</p>

                            <div class="flex items-center gap-6">
                                <div style="width:120px; height:120px;"
                                    class="rounded-xl overflow-hidden border bg-gray-100 flex items-center justify-center shrink-0">
                                    @if($glasses->primaryImage)
                                        <img src="{{ asset('storage/' . $glasses->primaryImage->path) }}"
                                            style="width:100%; height:100%; object-fit:cover;" alt="Main image">

                                    @else
                                        <span class="text-xs text-gray-500 text-center px-2">No image</span>
                                    @endif
                                </div>

                                <div class="flex-1 max-w-sm">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Replace main image
                                    </label>
                                    <input type="file" name="main_image" accept="image/*" class="block w-full text-sm file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0 file:text-sm file:font-semibold
                      file:bg-blue-600 file:text-white hover:file:bg-blue-700
                      border rounded-lg bg-gray-50">
                                </div>
                            </div>

                        </div>

                        {{-- Additional Images --}}
                        <div class="bg-white border rounded-xl p-6 shadow-sm mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-lg font-semibold text-gray-800">Additional Images</p>
                                    <p class="text-sm text-gray-600">Maximum 3 images allowed</p>
                                </div>

                                <span
                                    class="text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full px-4 py-1">
                                    {{ $glasses->images->where('is_primary', false)->count() }} / 3
                                </span>
                            </div>

                            <div class="flex gap-6 flex-wrap">
                                @forelse($glasses->images->where('is_primary', false) as $img)
                                    <div
                                        style="position:relative; width:120px; height:120px; overflow:hidden; border-radius:12px; border:1px solid #e5e7eb; background:#f3f4f6; display:inline-block;">
                                        <img src="{{ asset('storage/' . $img->path) }}"
                                            style="width:100%; height:100%; object-fit:cover; display:block;"
                                            alt="Additional image">

                                        <button type="button"
                                            onclick="document.getElementById('delete-image-{{ $img->id }}').submit()"
                                            style="position:absolute; top:8px; right:8px; z-index:9999;"
                                            class="bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-2 py-1 rounded-md shadow">
                                            Delete
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500 bg-gray-50 border rounded-xl p-6 w-full text-center">
                                        No additional images yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>





                        {{-- Upload Section --}}
                        <div class="bg-white border rounded-xl p-6 shadow-sm max-w-md">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Add more images
                            </label>

                            <input type="file" name="images[]" accept="image/*" multiple class="block w-full text-sm file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0 file:text-sm file:font-semibold
                      file:bg-gray-800 file:text-white hover:file:bg-gray-900
                      border rounded-lg bg-gray-50">

                            <p class="text-xs text-gray-500 mt-3">
                                If you already have 3 images, delete one before uploading a new one.
                            </p>
                        </div>
                    </div>


                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-lg mt-4">
                        ✅ Update Glasses
                    </button>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>