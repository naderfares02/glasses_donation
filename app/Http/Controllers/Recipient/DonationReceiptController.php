<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\DonationReceipt;
use Illuminate\Support\Facades\Storage;

class DonationReceiptController extends Controller
{
    private function ensureRecipient(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'recipient', 403);
    }

    public function index()
    {
        $this->ensureRecipient();

        $receipts = DonationReceipt::query()
            ->where('recipient_id', auth()->id())
            ->with(['glasses:id,title', 'donor:id,name'])
            ->latest('issued_at')
            ->paginate(12);

        return view('recipient.receipts.index', compact('receipts'));
    }

    public function show(DonationReceipt $receipt)
    {
        $this->ensureRecipient();
        $this->authorize('view', $receipt);

        $receipt->loadMissing(['donor', 'recipient', 'glasses', 'approver', 'donationRequest']);

        return view('recipient.receipts.show', compact('receipt'));
    }

    public function download(DonationReceipt $receipt)
    {
        $this->ensureRecipient();
        $this->authorize('download', $receipt);

        abort_if(!$receipt->pdf_path || !Storage::disk('local')->exists($receipt->pdf_path), 404);

        return Storage::disk('local')->download($receipt->pdf_path, $receipt->receipt_code . '.pdf');
    }
}
