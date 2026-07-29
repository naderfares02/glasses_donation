<?php

// app/Models/DeliveryConfirmation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'glasses_id','conversation_id','donor_id','recipient_id',
        'status','donor_note','recipient_note','recipient_responded_at','donation_request_id',
    ];

    public function glasses() { return $this->belongsTo(Glasses::class); }
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function donor() { return $this->belongsTo(User::class, 'donor_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
    public function donationRequest(){return $this->belongsTo(\App\Models\DonationRequest::class, 'donation_request_id');}
}