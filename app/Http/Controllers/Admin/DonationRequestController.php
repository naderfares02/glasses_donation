<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\DonorDonationApprovedNotification;
use App\Notifications\DonorDonationRejectedNotification;
use App\Models\DonationReceipt;
use App\Models\ContactRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DonationRequestController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $requests = DonationRequest::query()
            ->with([
                'glasses.primaryImage',
                'donor:id,name,email,avatar',
                'recipient:id,name,email,avatar',
            ])
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($q, function ($qq) use ($q) {
                $qq->whereHas('glasses', fn($g) => $g->where('title', 'like', "%{$q}%"))
                   ->orWhereHas('donor', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email','like',"%{$q}%"))
                   ->orWhereHas('recipient', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email','like',"%{$q}%"));
            })
            ->orderByRaw("CASE 
                WHEN status='pending' THEN 0
                WHEN status='approved' THEN 1
                WHEN status='rejected' THEN 2
                ELSE 3 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.donation_requests.index', compact('requests'));
    }

    public function show(DonationRequest $donationRequest)
    {
        $donationRequest->load([
            'glasses.primaryImage',
            'donor:id,name,email,avatar',
            'recipient:id,name,email,avatar',
            'deliveryConfirmation',
            // 'conversation.messages.sender', // إذا بدك تعرض جزء من المحادثة هنا
        ]);

        return view('admin.donation_requests.show', compact('donationRequest'));
    }



public function approve(Request $request, DonationRequest $donationRequest)
{
    // يسمح بالموافقة حتى لو كان مرفوض سابقاً
    if (!in_array($donationRequest->status, ['pending', 'rejected'], true)) {
        return back()->with('error', 'This request cannot be approved in its current status.');
    }

    // ✅ لازم المستفيد يكون أكد الاستلام فعلياً قبل ما الأدمن يوافق
    $confirmation = $donationRequest->deliveryConfirmation; // بدون أقواس

    if (!$confirmation || $confirmation->status !== 'received') {
        return back()->with('error', 'Cannot approve: recipient has not confirmed receiving the glasses yet.');
    }

    $data = $request->validate([
        'admin_note'      => ['nullable', 'string', 'max:2000'],
        'delivered_date'  => ['nullable', 'date'],
    ]);

    // لو يوجد إيصال بالفعل
    if ($donationRequest->receipt) {
        return back()->with('error', 'Receipt already exists for this request.');
    }

    $receipt = DB::transaction(function () use ($donationRequest, $data) {

        // 1) اعتماد الطلب
        $donationRequest->update([
            'status'      => 'approved',
            'admin_note'  => $data['admin_note'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // 2) تحديث النظارة (كان مكرر مرتين بالأصل، عدلتها لمرة وحدة)
        if ($donationRequest->glasses) {
            $donationRequest->glasses->update(['status' => 'donated']);
        }

        ContactRequest::where('glasses_id', $donationRequest->glasses_id)
            ->whereIn('status', ['pending', 'accepted', 'on_hold'])
            ->update([
                'status' => 'closed',
            ]);

        // 3) إنشاء الإيصال في DB
        $receipt = DonationReceipt::create([
            'donation_request_id' => $donationRequest->id,
            'glasses_id'          => $donationRequest->glasses_id,
            'donor_id'            => $donationRequest->donor_id,
            'recipient_id'        => $donationRequest->recipient_id,
            'approved_by'         => auth()->id(),
            'delivered_date'      => $donationRequest->delivered_date,
            'admin_note'          => $data['admin_note'] ?? null,
            'receipt_code'        => 'RCPT-' . strtoupper(Str::random(10)),
            'issued_at'           => now(),
        ]);

        return $receipt;
    });

    // ✅ توليد PDF خارج الترانزاكشن (أفضل)
    $receipt->loadMissing(['donor', 'recipient', 'glasses', 'approver']);

    $pdf = Pdf::loadView('receipts.pdf', [
        'receipt' => $receipt,
        'request' => $donationRequest,
    ])->setPaper('a4');

    $path = "receipts/{$receipt->receipt_code}.pdf";
    Storage::disk('public')->put($path, $pdf->output());

    $receipt->update(['pdf_path' => $path]);

    // إشعار المتبرع (صححت Notify -> notify، الحرف الكبير يعمل بالصدفة بـ PHP لأن أسماء الميثودز غير حساسة لحالة الأحرف، لكن الأفضل توحيدها بالحرف الصغير كباقي الكود)
    $donationRequest->loadMissing('donor');
    $donationRequest->donor->notify(new DonorDonationApprovedNotification($donationRequest));

    return redirect()
        ->route('admin.receipts.show', $receipt->id)
        ->with('success', 'Donation approved and receipt generated.');
}

    public function reject(Request $request, DonationRequest $donationRequest)
    {
        $data = $request->validate([
            'admin_note' => ['required','string','max:2000'],
        ]);

        if ($donationRequest->status !== 'pending') {
            return back()->with('error', 'This request is not pending.');
        }

        DB::transaction(function () use ($donationRequest, $data) {
            $donationRequest->update([
                'status' => 'rejected',
                'admin_note' => $data['admin_note'],
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            $confirmation = $donationRequest->deliveryConfirmation();

        //     if ($confirmation){
        //     $confirmation->update([
        //         'status' => 'not_received',
        //         'updated_at' => now(),
        //     ]);
        // }

            // ❗عند الرفض: ارجع النظارة متاحة (أو خلّيها in_contact حسب نظامك)
            $donationRequest->glasses->update([
                'status' => 'reserved',
                'active_contact_request_id' => null,
            ]);
        });

        $donationRequest->loadMissing('donor');
        $donationRequest->donor->notify(new DonorDonationRejectedNotification($donationRequest));

        return redirect()->route('admin.donation_requests.index')
            ->with('success', 'Donation request rejected.');
    }
}