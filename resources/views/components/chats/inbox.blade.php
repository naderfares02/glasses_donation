{{-- resources/views/components/chats/inbox.blade.php --}}

<div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 h-[650px]">

        {{-- =========================
        Sidebar (Conversations)
        ========================== --}}
        <aside class="lg:col-span-1 border-b lg:border-b-0 lg:border-r">
            <div class="p-4 bg-gray-50 border-b">
                <p class="text-sm font-semibold text-gray-700">Messages</p>
                <p class="text-xs text-gray-500 mt-1">Select a conversation to continue.</p>
            </div>

            <div class="max-h-[650px] overflow-y-auto">
                @forelse($conversations as $c)
                    @php
                        $other = auth()->id() === $c->donor_id ? $c->recipient : $c->donor;
                        $last = $c->messages->first();

                        $unread = \App\Models\Message::where('conversation_id', $c->id)
                            ->whereNull('read_at')
                            ->where('sender_id', '!=', auth()->id())
                            ->count();
                    @endphp

                    <button wire:click="setActive({{ $c->id }})" wire:key="conversation-{{ $c->id }}"
                        class="w-full text-left p-4 border-b hover:bg-gray-50 transition
                                                                                                                                                                                               {{ $activeConversationId === $c->id ? 'bg-blue-50' : '' }}">
                        <div class="flex gap-3">
                            {{-- Avatar --}}
                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border shrink-0 flex items-center justify-center">
                                @if($other?->avatar)
                                    <img src="{{ asset('storage/' . $other->avatar) }}" class="w-full h-full object-cover"
                                        alt="avatar">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-sm font-bold text-gray-700">
                                        {{ strtoupper(substr($other?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Text --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-gray-800 truncate">{{ $other?->name ?? 'User' }}</p>

                                    @if($unread > 0)
                                        <span class="text-xs font-bold text-white bg-red-600 rounded-full px-2 py-0.5">
                                            {{ $unread }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-500 truncate">
                                    {{ $c->glasses->title ?? 'Glasses' }}
                                </p>

                                <p class="text-sm text-gray-600 mt-1 truncate">
                                    {{ $last?->body ?? 'No messages yet' }}
                                </p>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="p-6 text-center text-gray-500">No conversations yet.</div>
                @endforelse
            </div>
        </aside>

        {{-- =========================
        Chat Window
        ========================== --}}
        <section class="lg:col-span-2 flex flex-col h-full min-h-0"
            x-data="{ openDonate:false, openReport:false, openActions:false }"
            @keydown.escape.window="openDonate=false; openReport=false; openActions=false">
            @if($active)
                    @php
                        $other = auth()->id() === $active->donor_id ? $active->recipient : $active->donor;
                    @endphp

                    {{-- Header --}}
                    <div class="p-4 border-b bg-gray-50 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 truncate">{{ $other?->name ?? 'User' }}</p>

                            @if (auth()->user()->role === 'recipient')
                                <p class="text-xs text-gray-500 truncate"><a
                                        href="{{ route('recipient.glasses.show', $active->glasses->id) }}">{{ $active->glasses->title ?? '' }}</a>
                                </p>
                            @else
                                <p class="text-xs text-gray-500 truncate"><a
                                        href="{{ route('donor.glasses.show', $active->glasses->id) }}">{{ $active->glasses->title ?? '' }}</a>
                                </p>
                            @endif

                        </div>

                        {{-- Right side actions --}}
                        <div class="flex items-center gap-2 shrink-0">

                            {{-- Status badge --}}
                            <span class="text-xs font-semibold px-3 py-1 rounded-full border
                                                                                                                                                                                                                                                                                                                                                                    {{ $active->status === 'open'
                ? 'bg-green-50 text-green-700 border-green-200'
                : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                {{ strtoupper($active->status) }}
                            </span>



                            {{-- Actions dropdown (Report + Disconnect) --}}
                            @php
                                $myComplaint = \App\Models\Complaint::where('conversation_id', $active->id)
                                    ->where('reporter_id', auth()->id())
                                    ->first();
                            @endphp

                            <div class="relative" x-data="{ openActions: false }">
                                <button type="button" @click="openActions = !openActions"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold text-gray-800">
                                    Actions
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-cloak x-show="openActions" @click.outside="openActions = false" x-transition
                                    class="absolute right-0 mt-2 w-52 bg-white border rounded-2xl shadow-lg overflow-hidden z-50">

                                    @if($myComplaint)
                                        <a href="{{ route('complaints.show', $myComplaint->id) }}"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-700 hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H7l-4 4V6a2 2 0 012-2z" />
                                            </svg>
                                            View Report
                                        </a>
                                    @else
                                        <button type="button" @click="openActions = false; openReport = true"
                                            class="w-full flex items-center gap-2 text-left px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v4m0 4h.01M5 21h14a2 2 0 001.732-3L13.732 4a2 2 0 00-3.464 0L3.268 18A2 2 0 005 21z" />
                                            </svg>
                                            Report
                                        </button>
                                    @endif
                                    @if (auth()->user()->role === 'donor')
                                        <form method="POST" action="{{ route('donor.conversations.disconnect', $active->id) }}"
                                            onsubmit="return confirm('Disconnect and make this glasses available again?');">
                                            @csrf
                                            <button type="submit"
                                                class="w-full flex items-center gap-2 text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M18.36 5.64a9 9 0 11-12.73 0M12 3v9" />
                                                </svg>
                                                Disconnect
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </div>

                            {{-- =========================
                            ✅ Complaint Modal
                            ========================== --}}
                            <div x-cloak x-show="openReport" x-transition
                                class="fixed inset-0 z-50 flex items-center justify-center px-4">
                                <div class="absolute inset-0 bg-black/40" @click="openReport=false"></div>

                                <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden">
                                    <div class="p-5 border-b bg-gray-50 flex items-start justify-between">
                                        <div>
                                            <p class="text-lg font-bold text-gray-800">Report an issue</p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                This will create a complaint to admins about this chat/listing.
                                            </p>
                                        </div>
                                        <button type="button" class="p-2 hover:bg-gray-100 rounded-xl"
                                            @click="openReport=false">✕</button>
                                    </div>
                                    <form method="POST"
                                        action="{{ route('complaints.store', ['conversation' => $active->id]) }}" class="p-5">
                                        @csrf

                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Reason</label>
                                                <select name="reason" required
                                                    class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-200">
                                                    <option value="">Select reason</option>
                                                    <option value="harassment">Harassment</option>
                                                    <option value="scam">Scam</option>
                                                    <option value="spam">Spam</option>
                                                    <option value="inappropriate_behavior">Inappropriate behavior</option>
                                                    <option value="other">Other</option>
                                                </select>
                                                @error('reason') <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Message</label>
                                                <textarea name="body" rows="5" required maxlength="3000"
                                                    class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-200"
                                                    placeholder="Write what happened..."></textarea>
                                                @error('body') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Extra details
                                                    (optional)</label>
                                                <textarea name="description" rows="3" maxlength="2000"
                                                    class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-200"
                                                    placeholder="Any extra details for the admin..."></textarea>
                                                @error('description') <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mt-6 flex items-center justify-end gap-3">
                                            <button type="button" @click="openReport=false"
                                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                                                Cancel
                                            </button>

                                            <button type="submit"
                                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                                                Submit Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>



                        </div>
                    </div>

                    {{-- Messages --}}
                    <div class="flex-1 min-h-0 p-5 overflow-y-auto space-y-3 bg-white" id="messages-container"
                        wire:key="messages-box">
                        @forelse($messages as $m)
                            @php $mine = $m->sender_id === auth()->id(); @endphp

                            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">

                                @php
                                    $role = auth()->user()->role ?? null;

                                    $myColor = match ($role) {
                                        'recipient' => 'bg-emerald-600 text-white',
                                        'donor' => 'bg-blue-600 text-white',
                                        'admin', 'super_admin' => 'bg-gray-900 text-white',
                                        default => 'bg-gray-600 text-white',
                                    };
                                @endphp

                                <div class="max-w-[75%] rounded-2xl px-4 py-3 {{ $mine ? $myColor : 'bg-gray-100 text-gray-800' }}">
                                    <p class="text-sm whitespace-pre-line">{{ $m->body }}</p>
                                    <p class="text-[11px] mt-2 opacity-75">
                                        {{ $m->created_at->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 text-sm py-10">No messages yet.</div>
                        @endforelse
                    </div>

                    @if($active->status === 'open' && auth()->user()->role === 'donor')
                        {{-- Mark as Donated --}}
                        <div class="px-5 py-3 border-t bg-white">
                            <button type="button" @click="openActions=false; openDonate=true;"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                                ✅ Mark as Delivered
                            </button>
                        </div>
                    @endif

                    {{-- =========================
                    Donor Donate Modal
                    ========================== --}}
                    @if(auth()->user()->role === 'donor' && $active->status === 'open')
                        <div x-cloak x-show="openDonate" x-transition
                            class="fixed inset-0 z-50 flex items-center justify-center px-4">
                            <div class="absolute inset-0 bg-black/40" @click="openDonate=false"></div>

                            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-lg font-bold text-gray-800">Request donation confirmation</p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            This will send a request to admin for review. Glasses will become
                                            <b>Pending</b>.
                                        </p>
                                    </div>
                                    <button type="button" class="p-2 hover:bg-gray-100 rounded-xl"
                                        @click="openDonate=false">✕</button>
                                </div>

                                <form method="POST" action="{{ route('donor.glasses.mark_donated', $active->glasses_id) }}"
                                    class="mt-5">
                                    @csrf
                                    <input type="hidden" name="conversation_id" value="{{ $active->id }}">

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                                Delivered date
                                            </label>
                                            <input type="date" name="delivered_date"
                                                class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-green-200"
                                                required>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                                Note for admin (optional)
                                            </label>
                                            <textarea name="donor_note" rows="4"
                                                class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-green-200"
                                                placeholder="Any details that help the admin verify the donation..."></textarea>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex items-center justify-end gap-3">
                                        <button type="button" @click="openDonate=false"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                                            Cancel
                                        </button>

                                        <button type="submit"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-green-600 hover:bg-green-700 text-white">
                                            Send to Admin
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Send box --}}
                    <div class="p-4 border-t bg-gray-50">
                        @if($active->status === 'open')
                            <form wire:submit.prevent="send" class="flex gap-3">
                                <textarea wire:model.defer="body" rows="2"
                                    class="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-200"
                                    placeholder="Write a message..."></textarea>

                                @if (auth()->user()->role === 'recipient')
                                    <button type="submit"
                                        class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 rounded-xl">
                                        Send
                                    </button>
                                @else
                                    <button type="submit"
                                        class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 rounded-xl">
                                        Send
                                    </button>
                                @endif
                            </form>

                            {{-- Clear input + auto-scroll to bottom on new message --}}
                            <script>
                                function scrollChatToBottom() {
                                    const el = document.getElementById('messages-container');
                                    if (el) el.scrollTop = el.scrollHeight;
                                }

                                document.addEventListener('livewire:init', () => {
                                    Livewire.on('clear-chat-box', () => {
                                        const el = document.querySelector('textarea[wire\\:model\\.defer="body"]');
                                        if (el) el.value = '';
                                    });

                                    Livewire.hook('morph.updated', ({ el }) => {
                                        if (el.id === 'messages-container' || el.querySelector?.('#messages-container')) {
                                            scrollChatToBottom();
                                        }
                                    });
                                });

                                document.addEventListener('DOMContentLoaded', scrollChatToBottom);
                            </script>

                            @error('body')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        @else
                            <p class="text-sm text-gray-600">This conversation is closed.</p>
                        @endif
                    </div>

            @else
                <div class="p-10 text-center text-gray-500">
                    Select a conversation from the left.
                </div>
            @endif
        </section>

    </div>
</div>