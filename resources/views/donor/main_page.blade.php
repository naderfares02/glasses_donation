<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Donor Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <h3 class="text-2xl font-bold mb-4">Welcome, generous donor ❤️</h3>
                <p class="text-gray-600 mb-6">
                    Here you can donate glasses to people who truly need them.
                    Every donation makes a difference.
                </p>

                <div class="flex justify-center gap-4 flex-wrap">
                    <!-- زر إضافة نظارة -->
                    <a href="{{ route('donor.glasses.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow">
                        ➕ Add New Glasses
                    </a>

                    <!-- زر عرض نظاراتي -->
                    <a href="{{ route('donor.glasses.index') }}"
                        class="bg-gray-700 hover:bg-gray-800 text-white font-semibold px-6 py-3 rounded-lg shadow">
                        📦 My Glasses
                    </a>
                </div>
            </div>


        </div>
    </div>
</x-app-layout>