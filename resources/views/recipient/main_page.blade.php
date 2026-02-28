@php use Illuminate\Support\Str; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Find Glasses
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Browse available listings and request contact when you find a match.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 text-sm text-gray-700">
                <span class="px-3 py-1 rounded-full bg-gray-100 border border-gray-200">
                    Total: <span class="font-semibold">{{ $glasses->total() }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                    <p class="font-semibold text-sm">Success</p>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Hero / Helper --}}
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-br from-blue-50 to-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="max-w-2xl">
                            <p class="text-xs font-semibold text-blue-700">How it works</p>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">
                                Request → Chat → Receive
                            </h3>
                            <p class="text-sm text-gray-700 mt-3 leading-relaxed">
                                Choose a listing that matches your needs. When you request contact and the donor
                                approves,
                                the item becomes reserved for you to avoid conflicts.
                            </p>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-white border">
                                    ✅ Verified donor chat
                                </span>
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-white border">
                                    🔒 Reserved after approval
                                </span>
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-white border">
                                    📦 Delivery confirmation
                                </span>
                            </div>
                        </div>

                        {{-- Optional filter/search UI (شكل فقط الآن) --}}
                        <div class="w-full lg:w-[420px]">
                            <div class="bg-white border rounded-2xl p-4 shadow-sm">
                                <p class="text-sm font-semibold text-gray-900">Search & filter</p>
                                <p class="text-xs text-gray-500 mt-1">Find what you need faster.</p>

                                <form method="GET" action="{{ route('recipient.main_page') }}"
                                    class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <input type="text" name="q" value="{{ request('q') }}"
                                            placeholder="Search title, lens type..."
                                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200">
                                    </div>

                                    <select name="condition"
                                        class="w-full border rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                        <option value="">All conditions</option>
                                        <option value="new" @selected(request('condition') === 'new')>New</option>
                                        <option value="used" @selected(request('condition') === 'used')>Used</option>
                                    </select>

                                    <select name="lens_type"
                                        class="w-full border rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-200">
                                        <option value="">All lens types</option>
                                        {{-- ضع القيم الحقيقية الموجودة عندك --}}
                                        <option value="single_vision" @selected(request('lens_type') === 'single_vision')>
                                            Single vision</option>
                                        <option value="progressive" @selected(request('lens_type') === 'progressive')>
                                            Progressive</option>
                                    </select>

                                    <div class="sm:col-span-2 flex gap-2">
                                        <button type="submit"
                                            class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white">
                                            Apply
                                        </button>

                                        <a href="{{ route('recipient.main_page') }}"
                                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 border text-gray-800">
                                            Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        {{-- /Optional --}}
                    </div>
                </div>
            </div>

            {{-- Empty --}}
            @if($glasses->count() === 0)
                <div class="bg-white border rounded-3xl p-10 text-center text-gray-700">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gray-100 border flex items-center justify-center text-2xl">
                        👓
                    </div>
                    <p class="mt-4 text-lg font-bold text-gray-900">No available glasses right now</p>
                    <p class="mt-1 text-sm text-gray-600">
                        Please check back later — new donations are added regularly.
                    </p>
                </div>
            @else
                {{-- Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($glasses as $item)
                        @php
                            $condBadge = $item->condition === 'new'
                                ? 'bg-green-50 text-green-700 border-green-200'
                                : 'bg-amber-50 text-amber-800 border-amber-200';

                            $summary = trim((string) $item->description);
                            $summary = $summary ? Str::limit($summary, 90) : null;
                        @endphp

                        <a href="{{ route('recipient.glasses.show', $item->id) }}"
                            class="group block bg-white border rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition">
                            {{-- Image --}}
                            <div class="relative h-52 bg-gray-100">
                                @if($item->primaryImage)
                                    <img src="{{ asset('storage/' . $item->primaryImage->path) }}"
                                        class="w-full h-full object-cover group-hover:scale-[1.02] transition" alt="Glasses image">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-500 text-sm">
                                        No image
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $condBadge }}">
                                        {{ strtoupper($item->condition) }}
                                    </span>
                                </div>

                                <div class="absolute bottom-3 right-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/90 border text-xs font-semibold text-gray-800">
                                        View →
                                    </span>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-bold text-gray-900 leading-snug line-clamp-2">
                                        {{ $item->title }}
                                    </h3>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <div class="flex flex-wrap gap-2">
                                        @if($item->lens_type)
                                            <span
                                                class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-50 border text-gray-700">
                                                Lens: {{ $item->lens_type }}
                                            </span>
                                        @endif

                                        @if($item->prescription)
                                            <span
                                                class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-50 border text-gray-700">
                                                Prescription: {{ $item->prescription }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($summary)
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            {{ $summary }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <p class="text-xs text-gray-500">
                                        Added: {{ $item->created_at?->format('Y-m-d') ?? '—' }}
                                    </p>

                                    <span class="text-xs font-semibold text-blue-700 group-hover:text-blue-800">
                                        Open details →
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $glasses->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>