<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Admin Dashboard</h2>
            <span class="text-sm text-gray-500">Overview</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Grid 3 ثم 3 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Card 1 --}}
                <a href="{{ route('admin.donation_requests.index') }}"
                    class="group bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Donation Requests</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">Review</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                            <span class="text-blue-700 font-bold">DR</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        Approve or reject donation confirmations.
                    </p>
                </a>

                {{-- Card 2 --}}
                <a href="{{ route('admin.users.index') }}"
                    class="group bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Users</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">Manage</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                            <span class="text-green-700 font-bold">U</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        View donors & recipients and take actions.
                    </p>
                </a>

                {{-- Card 3 --}}
                <a href="#" class="group bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Glasses</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">Overview</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                            <span class="text-purple-700 font-bold">G</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        Track available / pending / donated glasses.
                    </p>
                </a>

                {{-- Card 4 --}}
                <a href="#" class="group bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Contact Requests</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">Monitor</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center">
                            <span class="text-yellow-700 font-bold">CR</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        Follow ongoing contact processes.
                    </p>
                </a>

                {{-- Card 5 --}}
                <a href="#" class="group bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Reports</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">Stats</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <span class="text-red-700 font-bold">R</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        View system statistics and trends.
                    </p>
                </a>

                {{-- Card 6 --}}
                <a href="#" class="group bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Settings</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">Control</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                            <span class="text-gray-700 font-bold">S</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        Manage admins, policies, and system settings.
                    </p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>