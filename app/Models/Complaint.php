<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'glasses_id',
        'reporter_id',
        'reported_user_id',
        'reason',
        'description',
        'status',
        'handled_by',
        'resolution_note',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function glasses()
    {
        return $this->belongsTo(Glasses::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function messages()
    {
        return $this->hasMany(ComplaintMessage::class)->oldest();
    }
}