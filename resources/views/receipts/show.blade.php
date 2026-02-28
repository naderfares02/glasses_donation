<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Donation Receipt</h2>
                <p class="text-sm text-gray-500 mt-1">Receipt Code: <span
                        class="font-semibold">{{ $receipt->receipt_code }}</span></p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.receipts.download', $receipt->id) }}"
                    class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                    Download PDF
                </a>

                <a href="{{ url()->previous() }}"
                    class="px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Issued At</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $receipt->issued_at?->format('Y-m-d H:i') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Approved By</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $receipt->approver?->name ?? '—' }}</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="rounded-2xl border p-5">
                        <p class="text-xs text-gray-500">Donor</p>
                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $receipt->donor?->name }}</p>
                        <p class="text-sm text-gray-600">{{ $receipt->donor?->email }}</p>
                    </div>

                    <div class="rounded-2xl border p-5">
                        <p class="text-xs text-gray-500">Recipient</p>
                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $receipt->recipient?->name }}</p>
                        <p class="text-sm text-gray-600">{{ $receipt->recipient?->email }}</p>
                    </div>

                    <div class="rounded-2xl border p-5 sm:col-span-2">
                        <p class="text-xs text-gray-500">Glasses</p>
                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $receipt->glasses?->title ?? '—' }}</p>
                        <p class="text-sm text-gray-600 mt-1">
                            Delivered date: <span
                                class="font-semibold">{{ $receipt->delivered_date?->format('Y-m-d') ?? '—' }}</span>
                        </p>
                    </div>

                    <div class="rounded-2xl border p-5 sm:col-span-2">
                        <p class="text-xs text-gray-500">Admin note</p>
                        <p class="text-sm text-gray-800 mt-1">{{ $receipt->admin_note ?: '—' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>