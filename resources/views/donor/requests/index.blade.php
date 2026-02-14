<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">
                Contact Requests
            </h2>

            <a href="{{ route('donor.glasses.index') }}"
                class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back to My Glasses
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Glasses info --}}
            <div class="bg-white border rounded-xl p-6 shadow-sm mb-6">
                <p class="text-sm text-gray-500">Glasses</p>
                <p class="text-lg font-semibold text-gray-800">{{ $glasses->title }}</p>
                <p class="text-sm text-gray-500 mt-1">Status:
                    <span class="font-semibold">{{ ucfirst($glasses->status) }}</span>
                </p>
            </div>

            {{-- Requests List --}}
            <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="p-4">Recipient</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Requested At</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($requests as $request)
                            <tr class="border-b hover:bg-gray-50 @if($request->status === 'accepted') bg-green-50 @endif">
                                <td class="p-4 font-semibold text-gray-800">
                                    {{ $request->recipient->name }}
                                </td>

                                <td class="p-4 text-gray-600">
                                    {{ $request->recipient->email }}
                                </td>

                                <td class="p-4 text-gray-500">
                                    {{ $request->created_at->format('Y-m-d H:i') }}
                                </td>

                                <td class="p-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                                                                        @if($request->status === 'pending') bg-yellow-50 text-yellow-700 border border-yellow-200
                                                                                        @elseif($request->status === 'accepted') bg-green-50 text-green-700 border border-green-200
                                                                                        @elseif($request->status === 'rejected') bg-red-50 text-red-700 border border-red-200
                                                                                        @elseif($request->status === 'closed') bg-gray-100 text-gray-600 border border-gray-200
                                                                                        @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>

                                <td class="p-4 text-center">
                                    @if($request->status === 'pending' && !$glasses->active_contact_request_id)

                                        {{-- Accept --}}
                                        <form method="POST" action="{{ route('donor.requests.accept', $request->id) }}"
                                            class="inline">
                                            @csrf
                                            <button
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                                Accept
                                            </button>
                                        </form>

                                        {{-- Reject --}}
                                        <form method="POST" action="{{ route('donor.requests.reject', $request->id) }}"
                                            class="inline ml-2">
                                            @csrf
                                            <button
                                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                                Reject
                                            </button>
                                        </form>

                                    @elseif($request->status === 'accepted')

                                        {{-- NEW: Open Conversation Button --}}
                                        <a href="{{ route('donor.chats.index', ['conversation' => $request->conversation]) }}"
                                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                            Message Recipient
                                        </a>

                                    @else
                                        <span class="text-gray-400 text-sm">No actions</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-500">
                                    No contact requests yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>