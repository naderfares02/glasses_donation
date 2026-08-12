<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Notifications</h2>
                <p class="text-sm text-gray-500 mt-1">Stay updated with requests and messages.</p>
            </div>

            <form method="POST" action="{{ route('notifications.read_all') }}">
                @csrf
                <button type="submit"
                    class="text-sm font-semibold px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 border">
                    Mark all as read
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabs --}}
            <div class="bg-white border rounded-2xl shadow-sm p-4">
                <div class="flex items-center gap-2">
                    @if (auth()->user()->role === 'recipient')
                        <a href="{{ route('notifications.index', ['tab' => 'unread']) }}"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border
                                                      {{ $tab === 'unread' ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-white hover:bg-gray-50' }}">
                            Unread ({{ $counts['unread'] }})
                        </a>
                    @else
                        <a href="{{ route('notifications.index', ['tab' => 'unread']) }}"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border
                                                      {{ $tab === 'unread' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                            Unread ({{ $counts['unread'] }})
                        </a>
                    @endif

                    @if (auth()->user()->role === 'recipient')
                        <a href="{{ route('notifications.index', ['tab' => 'all']) }}"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border
                                              {{ $tab === 'all' ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-white hover:bg-gray-50' }}">
                            All ({{ $counts['all'] }})
                        </a>
                    @else
                        <a href="{{ route('notifications.index', ['tab' => 'all']) }}"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border
                                              {{ $tab === 'all' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                            All ({{ $counts['all'] }})
                        </a>
                    @endif

                </div>
            </div>

            {{-- List --}}
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-800">Your notifications</p>
                    <p class="text-xs text-gray-500">
                        Total: <span class="font-semibold">{{ $notifications->total() }}</span>
                    </p>
                </div>

                <div class="divide-y">
                    @forelse($notifications as $n)
                        @php
                            $data = $n->data ?? [];

                            $title = $data['title']
                                ?? $data['glasses_title']
                                ?? 'Notification';

                            $message = $data['body']
                                ?? $data['message']
                                ?? $data['text']
                                ?? 'You have a new notification.';

                            $url = $data['url'] ?? null;

                            $isUnread = is_null($n->read_at);
                        @endphp

                        <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="w-full">
                            @csrf

                            <button type="submit"
                                class="w-full text-left p-5 hover:bg-gray-50 transition {{ $isUnread ? 'bg-blue-50/40' : 'bg-white' }}">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ $title }}</p>

                                        <p class="text-sm text-gray-700 mt-1">
                                            {{ $message }}
                                        </p>

                                        {{-- @if($url)
                                        <p class="text-xs text-blue-700 mt-2 font-semibold">
                                            Click to open →
                                        </p>
                                        @endif --}}
                                    </div>

                                    <div class="flex flex-col items-end shrink-0">
                                        <p class="text-xs text-gray-500">
                                            {{ $n->created_at->diffForHumans() }}
                                        </p>

                                        @if($isUnread)
                                            <span
                                                class="mt-2 inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full bg-blue-600 text-white">
                                                New
                                            </span>
                                        @else
                                            <span
                                                class="mt-2 inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-700 border">
                                                Read
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        </form>
                    @empty
                        <div class="p-10 text-center text-gray-500">
                            No notifications.
                        </div>
                    @endforelse
                </div>

                <div class="p-5 border-t bg-gray-50">
                    {{ $notifications->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>