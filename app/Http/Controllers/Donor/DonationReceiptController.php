<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\DonationReceipt;
use Illuminate\Support\Facades\Storage;

class DonationReceiptController extends Controller
{
    private function ensureDonor(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'donor', 403);
    }

    public function index()
    {
        $this->ensureDonor();

        $receipts = DonationReceipt::query()
            ->where('donor_id', auth()->id())
            ->with(['glasses:id,title', 'recipient:id,name'])
            ->latest('issued_at')
            ->paginate(12);

        return view('donor.receipts.index', compact('receipts'));
    }

    public function show(DonationReceipt $receipt)
    {
        $this->ensureDonor();
        $this->authorize('view', $receipt);

        $receipt->loadMissing(['donor', 'recipient', 'glasses', 'approver', 'donationRequest']);

        return view('donor.receipts.show', compact('receipt'));
    }

    public function download(DonationReceipt $receipt)
    {
        $this->ensureDonor();
        $this->authorize('download', $receipt);

        abort_if(!$receipt->pdf_path || !Storage::disk('local')->exists($receipt->pdf_path), 404);

        return Storage::disk('local')->download($receipt->pdf_path, $receipt->receipt_code . '.pdf');
    }
}