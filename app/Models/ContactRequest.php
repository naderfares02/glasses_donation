<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    use HasFactory;

     protected $fillable = [
        'glasses_id','donor_id','recipient_id','status','accepted_at','closed_at'
    ];

    public function glasses() { return $this->belongsTo(Glasses::class); }
    public function donor() { return $this->belongsTo(User::class,'donor_id'); }
    public function recipient() { return $this->belongsTo(User::class,'recipient_id'); }
    public function conversation() { return $this->hasOne(Conversation::class); }

    
}
