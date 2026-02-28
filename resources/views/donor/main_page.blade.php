<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Donor Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome section --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-2xl p-8 shadow-sm">
                <h3 class="text-2xl font-bold">Welcome, generous donor ❤️</h3>
                <p class="mt-2 text-blue-100 max-w-2xl">
                    Your old glasses can change someone’s life.
                    Many people cannot afford proper vision care.
                    With a simple donation, you help someone study, work, and live better.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('donor.glasses.create') }}"
                        class="bg-white text-blue-700 font-semibold px-5 py-2.5 rounded-xl shadow hover:bg-blue-50">
                        ➕ Add New Glasses
                    </a>

                    <a href="{{ route('donor.glasses.index') }}"
                        class="bg-blue-800/40 hover:bg-blue-800/60 text-white font-semibold px-5 py-2.5 rounded-xl border border-white/20">
                        📦 My Glasses
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Total Listings</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">
                        {{ $stats['glasses_total'] ?? 0 }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-xs text-gray-500">In Contact</p>
                    <p class="text-2xl font-extrabold text-yellow-600 mt-1">
                        {{ $stats['in_contact'] ?? 0 }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Donated</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">
                        {{ $stats['donated'] ?? 0 }}
                    </p>
                </div>
            </div>

            {{-- Info section --}}
            {{-- How Donation Works --}}
            <section class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 border-b bg-gray-50">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg sm:text-xl font-extrabold text-gray-900">How donation works</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Follow these steps to donate safely and help someone see clearly.
                            </p>
                        </div>

                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                         bg-blue-50 text-blue-700 border border-blue-200">
                            Donor flow
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <ol class="relative grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Step 1 --}}
                        <li class="border rounded-2xl p-5 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                <div
                                    class="shrink-0 w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold">
                                    1
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900">Add your glasses listing</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Create a new listing with clear photos and honest details (condition, lens type,
                                        description).
                                        This helps recipients choose the right item.
                                    </p>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="{{ route('donor.glasses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                                      bg-blue-600 hover:bg-blue-700 text-white">
                                            ➕ Add New Glasses
                                        </a>

                                        <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold
                                         bg-gray-100 text-gray-700 border border-gray-200">
                                            Status: Available
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- Step 2 --}}
                        <li class="border rounded-2xl p-5 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                <div
                                    class="shrink-0 w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold">
                                    2
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900">A recipient requests contact</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        When someone needs your glasses, they send a contact request.
                                        Once you approve the request, the item is reserved exclusively for that
                                        recipient to avoid conflicts.
                                    </p>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold
                                         bg-purple-50 text-purple-700 border border-purple-200">
                                            Status: Reserved
                                        </span>
                                        <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold
                                         bg-gray-100 text-gray-700 border border-gray-200">
                                            You’ll see a new chat
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- Step 3 --}}
                        <li class="border rounded-2xl p-5 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                <div
                                    class="shrink-0 w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold">
                                    3
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900">Chat and agree on delivery</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Use the chat to agree on a safe delivery plan:
                                    </p>

                                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                                        <li class="flex items-start gap-2">
                                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            Choose a meeting point or delivery method.
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            Agree on date/time.
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            Confirm details before meeting.
                                        </li>
                                    </ul>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold
                                         bg-yellow-50 text-yellow-700 border border-yellow-200">
                                            Status: In contact
                                        </span>

                                        <a href="{{ route('donor.chats.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                                      bg-white hover:bg-gray-50 text-gray-800 border">
                                            💬 Open chats
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- Step 4 --}}
                        <li class="border rounded-2xl p-5 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                <div
                                    class="shrink-0 w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold">
                                    4
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900">Confirm donation</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        After delivery, click <span class="font-semibold">Mark as donated</span>.
                                        The system will notify the recipient to confirm receiving it.
                                        If admin approval is enabled, admins will review it too.
                                    </p>

                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                                            <p class="text-[11px] font-semibold text-amber-700">Pending donation</p>
                                            <p class="text-xs text-amber-800 mt-1">Waiting confirmation/review</p>
                                        </div>

                                        <div class="p-3 rounded-xl bg-green-50 border border-green-200">
                                            <p class="text-[11px] font-semibold text-green-700">Approved</p>
                                            <p class="text-xs text-green-800 mt-1">Donation completed ✅</p>
                                        </div>

                                        <div class="p-3 rounded-xl bg-red-50 border border-red-200">
                                            <p class="text-[11px] font-semibold text-red-700">Rejected</p>
                                            <p class="text-xs text-red-800 mt-1">Needs review / issue</p>
                                        </div>
                                    </div>

                                    <p class="mt-4 text-xs text-gray-500">
                                        Tip: keep communication respectful and follow safety guidelines during delivery.
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ol>

                    {{-- Optional: Safety note --}}
                    <div class="mt-6 p-5 rounded-2xl border bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Safety & privacy tips</p>
                        <ul class="mt-3 space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                Meet in a public place when possible.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                Avoid sharing sensitive personal details in chat.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                Use the platform confirmation to complete the donation.
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>