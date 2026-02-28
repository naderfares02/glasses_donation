<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationReceipt extends Model
{
    protected $fillable = [
        'donation_request_id',
        'glasses_id',
        'donor_id',
        'recipient_id',
        'approved_by',
        'delivered_date',
        'admin_note',
        'receipt_code',
        'pdf_path',
        'issued_at',
    ];

    protected $casts = [
        'delivered_date' => 'date',
        'issued_at'      => 'datetime',
    ];

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class);
    }

    public function glasses()
    {
        return $this->belongsTo(Glasses::class);
    }

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}