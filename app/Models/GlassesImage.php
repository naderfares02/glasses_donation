<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlassesImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'glasses_id',
        'path',
        'is_primary',
    ];
    public function glasses()
{
    return $this->belongsTo(Glasses::class);
}

}
