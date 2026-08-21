<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">My Receipts</h2>
                <p class="text-sm text-gray-500 mt-1">Donation receipts issued for glasses you've received.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="divide-y">
                    @forelse($receipts as $receipt)
                        <div class="p-5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-mono font-bold text-gray-900">{{ $receipt->receipt_code }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $receipt->glasses?->title ?? '—' }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    From {{ $receipt->donor?->name ?? '—' }} ·
                                    Issued {{ $receipt->issued_at?->format('Y-m-d') ?? '—' }}
                                </p>
                            </div>

                            <div class="flex gap-2 shrink-0">
                                <a href="{{ route('recipient.receipts.show', $receipt->id) }}"
                                    class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                                    View
                                </a>
                                <a href="{{ route('recipient.receipts.download', $receipt->id) }}"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                    Download
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-500 text-sm">
                            No receipts yet.
                        </div>
                    @endforelse
                </div>
            </div>

            {{ $receipts->links() }}
        </div>
    </div>
</x-app-layout>
