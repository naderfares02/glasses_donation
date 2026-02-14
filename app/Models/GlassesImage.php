<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlassesImage extends Model
{

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
