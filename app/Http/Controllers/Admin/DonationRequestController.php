<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\DonationReceipt;
use App\Models\DonationRequest;
use App\Notifications\DonorDonationApprovedNotification;
use App\Notifications\DonorDonationRejectedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonationRequestController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'], true), 403);
    }

    public function index(Request $request)
    {

        $this->ensureAdmin();

        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $requests = DonationRequest::query()
            ->with([
                'glasses.primaryImage',
                'donor:id,name,email,avatar',
                'recipient:id,name,email,avatar',
            ])
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($group) use ($q) {
                    $group->whereHas('glasses', fn ($g) => $g->where('title', 'like', "%{$q}%"))
                        ->orWhereHas('donor', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                        ->orWhereHas('recipient', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
                });
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

        $this->ensureAdmin();

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

        $this->ensureAdmin();

        if (! in_array($donationRequest->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'This request cannot be approved in its current status.');
        }

        $confirmation = $donationRequest->deliveryConfirmation;

        if (! $confirmation || $confirmation->status !== 'received') {
            return back()->with('error', 'Cannot approve: recipient has not confirmed receiving the glasses yet.');
        }

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'delivered_date' => ['nullable', 'date'],
        ]);

        try {
            $receipt = DB::transaction(function () use ($donationRequest, $data) {

                // ✅ قفل الصف وإعادة التحقق من الحالة جوا الـ transaction
                $locked = DonationRequest::whereKey($donationRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($locked->status, ['pending', 'rejected'], true)) {
                    throw new \RuntimeException('ALREADY_PROCESSED');
                }

                if ($locked->receipt) {
                    throw new \RuntimeException('RECEIPT_EXISTS');
                }

                // 1) اعتماد الطلب — ما لازم تنسى هاي الخطوة!
                $locked->update([
                    'status' => 'approved',
                    'admin_note' => $data['admin_note'] ?? null,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                ]);

                // 2) تحديث النظارة
                if ($locked->glasses) {
                    $locked->glasses->update(['status' => 'donated']);
                }

                // 3) إقفال طلبات التواصل المرتبطة
                ContactRequest::where('glasses_id', $locked->glasses_id)
                    ->whereIn('status', ['pending', 'accepted', 'on_hold'])
                    ->update(['status' => 'closed']);

                // 4) إنشاء الإيصال — استخدم $locked مش $donationRequest
                $receipt = DonationReceipt::create([
                    'donation_request_id' => $locked->id,
                    'glasses_id' => $locked->glasses_id,
                    'donor_id' => $locked->donor_id,
                    'recipient_id' => $locked->recipient_id,
                    'approved_by' => auth()->id(),
                    'delivered_date' => $locked->delivered_date,
                    'admin_note' => $data['admin_note'] ?? null,
                    'receipt_code' => 'RCPT-'.strtoupper(Str::random(10)),
                    'issued_at' => now(),
                ]);

                return $receipt;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', match ($e->getMessage()) {
                'RECEIPT_EXISTS' => 'Receipt already exists for this request.',
                'ALREADY_PROCESSED' => 'This request was already processed by another action.',
                default => throw $e,
            });
        }

        $donationRequest->refresh();

        try {
            $pdf = Pdf::loadView('receipts.pdf', [
                'receipt' => $receipt->loadMissing(['donor', 'recipient', 'glasses', 'approver']),
                'request' => $donationRequest,
            ])->setPaper('a4');

            $path = "receipts/{$receipt->receipt_code}.pdf";
            Storage::disk('local')->put($path, $pdf->output()); // local بدل public (راجع ملاحظة القرص العام بالرد السابق)
            $receipt->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            report($e); // سجل الخطأ بالـ logs، ما توقف تنفيذ الطلب

            return redirect()
                ->route('admin.receipts.show', $receipt->id)
                ->with('warning', 'Donation was approved, but the PDF receipt could not be generated. Please retry generating it.');
        }

        $donationRequest->loadMissing('donor');

        try {
            $donationRequest->donor?->notify(new DonorDonationApprovedNotification($donationRequest));
        } catch (\Throwable $e) {
            report($e); // فشل الإشعار ما لازم يفشّل كل العملية
        }

        return redirect()
            ->route('admin.receipts.show', $receipt->id)
            ->with('success', 'Donation approved and receipt generated.');
    }



    /**
     * تجاوز إداري: تحويل حالة تأكيد الاستلام من "لم يستلم" إلى "استلم"
     * يُستخدم فقط عندما يكون الأدمن متأكداً (عبر الشكوى/التواصل) أن المستفيد
     * استلم فعلياً رغم أنه أنكر ذلك. يُسجَّل من قام بالتجاوز والسبب إلزامياً.
     */
    public function overrideConfirmation(Request $request, DonationRequest $donationRequest)
    {
        $confirmation = $donationRequest->deliveryConfirmation;

        if (!$confirmation) {
            return back()->with('error', 'No delivery confirmation found for this request.');
        }

        if ($confirmation->status !== 'not_received') {
            return back()->with('error', 'Override is only allowed when the recipient has denied receiving the item.');
        }

        $data = $request->validate([
            'override_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $confirmation->update([
            'status'          => 'received',
            'overridden_by'   => auth()->id(),
            'override_reason' => $data['override_reason'],
            'overridden_at'   => now(),
        ]);

        return back()->with('success', 'Confirmation overridden to "received". You can now approve the donation request.');
    }

    
    public function reject(Request $request, DonationRequest $donationRequest)
    {

        $this->ensureAdmin();

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        if ($donationRequest->status !== 'pending') {
            return back()->with('error', 'This request is not pending.');
        }

        try {
            DB::transaction(function () use ($donationRequest, $data) {

                // ✅ قفل الصف وإعادة التحقق من الحالة جوا الـ transaction
                // (يمنع تعارض approve/reject لنفس الطلب بنفس اللحظة)
                $locked = DonationRequest::whereKey($donationRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== 'pending') {
                    throw new \RuntimeException('NOT_PENDING');
                }

                $locked->update([
                    'status' => 'rejected',
                    'admin_note' => $data['admin_note'],
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                ]);

                $confirmation = $locked->deliveryConfirmation;

                if ($confirmation && $confirmation->status === 'received') {
                    // المستفيد استلم النظارة فعلياً بإيده قبل ما يرفض الأدمن الطلب.
                    // ما نرجعها "available" لأنها مش فاضية فعلياً — منستخدم حالة
                    // "reserved" الموجودة أصلاً عشان تختفي من قائمة المتاح للجمهور
                    // وتضل بحاجة متابعة يدوية من الأدمن مع المستفيد، بدل ما يقدر
                    // مستفيد تاني يطلبها ويوعد فيها وهي أصلاً بحوزة حدا.
                    $locked->glasses->update([
                        'status' => 'reserved',
                    ]);
                    // ملاحظة: ما بنلغي active_contact_request_id ولا نرجّع طلبات
                    // on_hold لـ pending هون، لأن النظارة لسا "محجوزة" فعلياً على
                    // نفس المستفيد لحد ما الأدمن يتابع الموضوع يدوياً ويقرر.
                } else {
                    // ما في استلام فعلي — آمن نرجعها متاحة للجمهور من جديد
                    $locked->glasses->update([
                        'status' => 'available',
                        'active_contact_request_id' => null,
                    ]);

                    ContactRequest::where('glasses_id', $locked->glasses_id)
                        ->where('status', 'on_hold')
                        ->update(['status' => 'pending']);
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'NOT_PENDING') {
                return back()->with('error', 'This request was already processed by another action.');
            }
            throw $e;
        }

        $donationRequest->refresh();
        $donationRequest->loadMissing('donor');
        $donationRequest->donor->notify(new DonorDonationRejectedNotification($donationRequest));

        return redirect()->route('admin.donation_requests.index')
            ->with('success', 'Donation request rejected.');
    }
}
