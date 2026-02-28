<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationReceipt;
use Illuminate\Support\Facades\Storage;

class DonationReceiptController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin','super_admin'], true), 403);
    }

    public function show(DonationReceipt $receipt)
{
    $this->ensureAdmin();
    $this->authorize('view', $receipt);

    $receipt->loadMissing(['donor', 'recipient', 'glasses', 'approver', 'donationRequest']);

    return view('admin.receipts.show', compact('receipt'));
}

public function download(DonationReceipt $receipt)
{
    $this->ensureAdmin();
    $this->authorize('download', $receipt);

    abort_if(!$receipt->pdf_path || !Storage::disk('public')->exists($receipt->pdf_path), 404);

    return Storage::disk('public')->download($receipt->pdf_path, $receipt->receipt_code . '.pdf');
}
}