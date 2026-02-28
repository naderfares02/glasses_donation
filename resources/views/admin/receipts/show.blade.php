@include('receipts.show', [
    'receipt' => $receipt,
    'downloadUrl' => route('admin.receipts.download', $receipt->id),
])