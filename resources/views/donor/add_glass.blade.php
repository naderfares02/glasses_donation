<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Glasses for Donation
        </h2>
    </x-slot>

    <div class="p-5">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-5 rounded-lg shadow-md">
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('donor.glasses.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Primary Image -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Main Image</label>
                        <input type="file" name="main_image" accept="image/*" required
                            class="w-full border-gray-300 rounded-lg shadow-sm">
                    </div>

                    <!-- Additional Images -->
                    <div class="mb-6">
                        <label class="block font-medium mb-1">Additional Images (Max 3)</label>
                        <input type="file" name="images[]" accept="image/*" multiple
                            class="w-full border-gray-300 rounded-lg shadow-sm">
                    </div>


                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Title</label>
                        <input type="text" name="title" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm"></textarea>
                    </div>

                    <!-- Lens Type -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Lens Type</label>
                        <input type="text" name="lens_type" class="w-full border-gray-300 rounded-lg shadow-sm">
                    </div>

                    <!-- Prescription -->
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Prescription</label>
                        <input type="text" name="prescription" class="w-full border-gray-300 rounded-lg shadow-sm">
                    </div>

                    <!-- Condition -->
                    <div class="mb-6">
                        <label class="block font-medium mb-1">Condition</label>
                        <select name="condition" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                            <option value="new">New</option>
                            <option value="used">Used</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg">
                        💾 Save Glasses
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>