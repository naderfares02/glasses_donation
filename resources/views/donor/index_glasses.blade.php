<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Glasses
        </h2>
    </x-slot>

    <div class="p-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('donor.glasses.create') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg">
                    ➕ Add New Glasses
                </a>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3">image</th>
                            <th class="p-3">Title</th>
                            <th class="p-3">Condition</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Created</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-50 text-center">
                        @forelse($glasses as $item)
                            <tr class="border-t">
                                <td class="p-3 align-middle">
                                    @if($item->primaryImage)
                                        <img src="{{ asset('storage/' . $item->primaryImage->path) }}"
                                            class="w-14 h-14 object-cover rounded-md border inline-block" alt="Glasses image">
                                    @endif
                                </td>


                                <td class="p-3">{{ $item->title }}</td>
                                <td class="p-3">{{ ucfirst($item->condition) }}</td>
                                <td class="p-3">
                                    @php
                                        $status = $item->status;

                                        $styles = match ($status) {
                                            'available' => 'text-green-700 bg-green-50 border-green-200',
                                            'in_contact' => 'text-yellow-700 bg-yellow-50 border-yellow-200',
                                            'donated' => 'text-blue-700 bg-blue-50 border-blue-200',
                                            default => 'text-gray-700 bg-gray-100 border-gray-200',
                                        };
                                    @endphp

                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $styles }}">

                                        {{-- Icon --}}
                                        @if($status === 'available')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @elseif($status === 'in_contact')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 10h8M8 14h5m9-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @elseif($status === 'donated')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v13m0-13c-1.657 0-3-1.343-3-3h6c0 1.657-1.343 3-3 3z" />
                                            </svg>
                                        @endif

                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td class="p-3">{{ $item->created_at->format('Y-m-d') }}</td>
                                <td class="p-3">
                                    <div x-data="{
                                                                open:false,
                                                                top:0,
                                                                left:0,
                                                                toggle(e){
                                                                    this.open = !this.open;
                                                                    if(this.open){
                                                                        const r = e.currentTarget.getBoundingClientRect();
                                                                        this.top = r.bottom + 8;
                                                                        this.left = r.right - 160; // 160 = عرض القائمة تقريباً
                                                                    }
                                                                }
                                                            }" class="inline-block">

                                        <button @click="toggle($event)"
                                            class=" p-2 rounded-md hover:bg-gray-200 transition  ">
                                            <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </button>


                                        <div x-show="open" @click.outside="open=false" x-transition
                                            :style="`position:fixed; top:${top}px; left:${left}px; width:160px;`"
                                            class="bg-white border rounded-lg shadow-lg z-50">
                                            <div class="py-1 text-center">
                                                <a href="{{ route('donor.glasses.show', $item->id) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View</a>

                                                <a href="{{ route('donor.glasses.edit', $item->id) }}"
                                                    class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Edit</a>

                                                <form action="{{ route('donor.glasses.destroy', $item->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>


                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 text-gray-500" colspan="4">No glasses added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $glasses->links() }}
            </div>

        </div>
    </div>
</x-app-layout>