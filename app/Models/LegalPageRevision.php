<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPageRevision extends Model
{
    public $timestamps = false; // بس created_at، ما فيه updated_at

    protected $fillable = [
        'legal_page_id',
        'content',
        'updated_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function legalPage()
    {
        return $this->belongsTo(LegalPage::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}