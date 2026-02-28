@include('receipts.show', [
    'receipt' => $receipt,
    'downloadUrl' => route('donor.receipts.download', $receipt->id),
])