<div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 min-h-[650px]">

        {{-- Sidebar --}}
        <div class="lg:col-span-1 border-b lg:border-b-0 lg:border-r">
            <div class="p-4 bg-gray-50 border-b">
                <p class="text-sm font-semibold text-gray-700">Messages</p>
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

                    <button wire:click="setActive({{ $c->id }})"
                        class="w-full text-left p-4 border-b hover:bg-gray-50 {{ $activeConversationId === $c->id ? 'bg-blue-50' : '' }}">
                        <div class="flex gap-3">
                            @php
                                $other = auth()->id() === $c->donor_id ? $c->recipient : $c->donor;
                            @endphp

                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border shrink-0 flex items-center justify-center">
                                @if($other->avatar)
                                    <img src="{{ asset('storage/' . $other->avatar) }}" class="w-full h-full object-cover"
                                        alt="avatar">
                                @else
                                    <div class="w-full h-full flex items-center justify-center
                                                                    text-sm font-bold text-gray-700">
                                        {{ strtoupper(substr($other->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-gray-800 truncate">{{ $other->name }}</p>
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
        </div>

        {{-- Chat Window --}}
        <div class="lg:col-span-2 flex flex-col">
            @if($active)
                @php
                    $other = auth()->id() === $active->donor_id ? $active->recipient : $active->donor;
                @endphp

                <div class="p-4 border-b bg-gray-50 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $other->name }}</p>
                        <p class="text-xs text-gray-500">{{ $active->glasses->title ?? '' }}</p>
                    </div>

                    <div x-data="{ openDonate:false }" class="flex items-center gap-3">
                        {{-- Status badge --}}
                        <span
                            class="text-xs font-semibold px-3 py-1 rounded-full
                                    {{ $active->status === 'open' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                            {{ strtoupper($active->status) }}
                        </span>

                        @if(auth()->user()->role === 'donor' && $active->status === 'open')

                            {{-- زر يفتح الفورم --}}
                            <button type="button" @click="openDonate = true"
                                class="text-xs font-semibold px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white">
                                Mark as Donated
                            </button>

                            {{-- Disconnect --}}
                            <form method="POST" action="{{ route('donor.conversations.disconnect', $active->id) }}"
                                onsubmit="return confirm('Disconnect and make this glasses available again?');">
                                @csrf
                                <button type="submit"
                                    class="text-xs font-semibold px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white">
                                    Disconnect
                                </button>
                            </form>

                            {{-- Modal --}}
                            <div x-show="openDonate" x-transition class="fixed inset-0 z-50 flex items-center justify-center">

                                {{-- overlay --}}
                                <div class="absolute inset-0 bg-black/40" @click="openDonate=false"></div>

                                {{-- modal card --}}
                                <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-lg font-bold text-gray-800">Request donation confirmation</p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                This will send a request to admin for review. Glasses will become
                                                <b>Pending</b>.
                                            </p>
                                        </div>
                                        <button class="p-2 hover:bg-gray-100 rounded-lg" @click="openDonate=false">✕</button>
                                    </div>

                                    <form method="POST" action="{{ route('donor.glasses.mark_donated', $active->glasses_id) }}"
                                        class="mt-5">
                                        @csrf
                                        <input type="hidden" name="conversation_id" value="{{ $active->id }}">

                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Delivered date
                                                    (optional)</label>
                                                <input type="date" name="delivered_date"
                                                    class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-green-200">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Note for admin
                                                    (optional)</label>
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
                    </div>

                </div>


                <div class="flex-1 p-5 overflow-y-auto space-y-3 bg-white">
                    @forelse($messages as $m)
                        @php $mine = $m->sender_id === auth()->id(); @endphp
                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <div
                                class="max-w-[75%] rounded-2xl px-4 py-3
                                                                                                                        {{ $mine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
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

                <div class="p-4 border-t bg-gray-50">
                    @if($active->status === 'open')
                        <form wire:submit.prevent="send" class="flex gap-3">
                            <textarea wire:model.defer="body" rows="2"
                                class="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-200"
                                placeholder="Write a message..."></textarea>

                            <button type="submit"
                                class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 rounded-xl">
                                Send
                            </button>
                        </form>

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
        </div>

    </div>
</div>