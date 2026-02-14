@php use Illuminate\Support\Facades\Storage; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Available Glasses
            </h2>

            <div class="text-sm text-gray-600">
                Total: <span class="font-semibold">{{ $glasses->total() }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-700 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if($glasses->count() === 0)
                <div class="bg-white border rounded-xl p-10 text-center text-gray-600">
                    No available glasses at the moment.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                    @foreach($glasses as $item)
                        <a href="{{ route('recipient.glasses.show', $item->id) }}" class="...">
                            <div class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
                                {{-- Image --}}
                                <div class="h-48 bg-gray-100">
                                    @if($item->primaryImage)
                                        <img src="{{ asset('storage/' . $item->primaryImage->path) }}"
                                            class="w-full h-full object-cover" alt="Glasses image">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-500 text-sm">
                                            No image
                                        </div>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-semibold text-gray-800 leading-snug">
                                            {{ $item->title }}
                                        </h3>

                                        <span
                                            class="text-xs font-semibold px-2 py-1 rounded-full
                                                                        {{ $item->condition === 'new' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-yellow-50 text-yellow-700 border border-yellow-200' }}">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </div>

                                    @if($item->lens_type)
                                        <p class="text-sm text-gray-600 mt-2">
                                            Lens: <span class="font-medium">{{ $item->lens_type }}</span>
                                        </p>
                                    @endif

                                    @if($item->prescription)
                                        <p class="text-sm text-gray-600 mt-1">
                                            Prescription: <span class="font-medium">{{ $item->prescription }}</span>
                                        </p>
                                    @endif

                                    <p class="text-xs text-gray-500 mt-3">
                                        Added: {{ $item->created_at->format('Y-m-d') }}
                                    </p>

                                    {{-- <div class="mt-4">
                                        <a href="{{ route('recipient.glasses.show', $item->id) }}"
                                            class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                            View Details
                                        </a>
                                    </div> --}}
                                </div>
                            </div>
                        </a>
                    @endforeach

                </div>

                <div class="mt-8">
                    {{ $glasses->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>