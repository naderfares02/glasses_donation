<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationRequest extends Model
{
    protected $fillable = [
    'glasses_id','conversation_id','donor_id','recipient_id',
    'status','delivered_date','donor_note','admin_note',
    'reviewed_at','reviewed_by',
];

public function glasses()
{
    return $this->belongsTo(\App\Models\Glasses::class);
}

public function donor()
{
    return $this->belongsTo(\App\Models\User::class, 'donor_id');
}

public function recipient()
{
    return $this->belongsTo(\App\Models\User::class, 'recipient_id');
}

public function conversation()
{
    return $this->belongsTo(\App\Models\Conversation::class, 'conversation_id');
}

public function deliveryConfirmation()
{
    return $this->hasOne(DeliveryConfirmation::class);
}

public function receipt()   { return $this->hasOne(DonationReceipt::class); }




}
