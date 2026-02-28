<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['contact_request_id','glasses_id','donor_id','recipient_id','status'];

    public function request() { return $this->belongsTo(ContactRequest::class,'contact_request_id'); }
    public function messages() { return $this->hasMany(Message::class); }

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

}
