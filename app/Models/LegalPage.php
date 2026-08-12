<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'key','content','updated_by','published_at'
    ];
    protected $casts = [
        'published_at' => 'datetime',
    ];
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}