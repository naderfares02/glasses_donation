@php use Illuminate\Support\Facades\Storage; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Glasses Details
            </h2>

            <a href="{{ route('recipient.main_page') }}"
                class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to home
            </a>
        </div>
    </x-slot>


    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    {{-- LEFT: Images --}}
                    <div class="p-6 border-b lg:border-b-0 lg:border-r">
                        {{-- Main Image --}}
                        <div
                            class="w-full h-80 bg-gray-100 rounded-xl overflow-hidden flex items-center justify-center">
                            @if($glasses->primaryImage)
                                <img src="{{ asset('storage/' . $glasses->primaryImage->path) }}"
                                    class="w-full h-full object-cover" alt="Main image">
                            @else
                                <div class="text-gray-500 text-sm">No image</div>
                            @endif
                        </div>

                        {{-- Thumbnails --}}
                        @php
                            $additional = $glasses->images->where('is_primary', false);
                        @endphp

                        @if($additional->count())
                            <div class="mt-4 flex gap-3 flex-wrap">
                                @foreach($additional as $img)
                                    <a href="{{ asset('storage/' . $img->path) }}" target="_blank"
                                        class="block w-20 h-20 rounded-lg overflow-hidden border bg-gray-100 hover:shadow transition">
                                        <img src="{{ asset('storage/' . $img->path) }}" class="w-full h-full object-cover"
                                            alt="Additional image">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT: Details --}}
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">
                                    {{ $glasses->title }}
                                </h1>
                                <p class="text-sm text-gray-500 mt-1">
                                    Added: {{ $glasses->created_at->format('Y-m-d') }}
                                </p>
                            </div>

                            <span class="text-xs font-semibold px-3 py-1 rounded-full
                                {{ $glasses->condition === 'new'
    ? 'bg-green-50 text-green-700 border border-green-200'
    : 'bg-yellow-50 text-yellow-700 border border-yellow-200' }}">
                                {{ ucfirst($glasses->condition) }}
                            </span>
                        </div>

                        {{-- Info cards --}}
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Lens Type</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $glasses->lens_type ?? '—' }}
                                </p>
                            </div>

                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Prescription</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $glasses->prescription ?? '—' }}
                                </p>
                            </div>

                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Status</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $glasses->status)) }}
                                </p>
                            </div>

                            <div class="border rounded-xl p-4 bg-gray-50">
                                <p class="text-xs text-gray-500">Donor Name</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ $glasses->user->name ?? 'Unknown' }}
                                </p>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mt-6">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Description</p>
                            <div class="text-sm text-gray-700 leading-relaxed bg-gray-50 border rounded-xl p-4">
                                {{ $glasses->description ?: 'No description provided.' }}
                            </div>
                        </div>

                        {{-- CTA --}}
                        <div class="mt-6 flex gap-3">
                            @if($glasses->status === 'available')

                                <form method="POST" action="{{ route('recipient.contact-requests.store', $glasses->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg">
                                        Request Contact
                                    </button>
                                </form>

                            @elseif($glasses->status === 'pending_donation')

                                <div class="inline-flex items-center gap-2 px-6 py-3 rounded-lg
                                                       bg-gray-50 text-gray-700 border border-gray-200
                                                       font-semibold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                                        <circle cx="12" cy="12" r="9" />
                                    </svg>
                                    Pending
                                </div>

                            @elseif($glasses->status === 'donated')

                                <div class="inline-flex items-center gap-2 px-6 py-3 rounded-lg
                                                       bg-green-50 text-green-700 border border-green-200
                                                       font-semibold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Donated
                                </div>

                            @endif


                            {{-- <a href="{{ route('recipient.main_page') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold px-6 py-3 rounded-lg">
                                Back
                            </a> --}}
                        </div>

                        {{-- <p class="text-xs text-gray-500 mt-3">
                            * In the next step, we will implement the request/approval flow.
                        </p> --}}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>