@include('receipts.show', [
    'receipt' => $receipt,
    'downloadUrl' => route('recipient.receipts.download', $receipt->id),
])
