<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glasses extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'lens_type',
        'prescription',
        'condition',
        'status',
    ];
    public function donor()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function images()
{
    return $this->hasMany(GlassesImage::class);
}

public function primaryImage()
{
    return $this->hasOne(GlassesImage::class)->where('is_primary', true);
}

public function contactRequests()
{
    return $this->hasMany(ContactRequest::class);
}

public function activeContactRequest()
{
    return $this->belongsTo(ContactRequest::class, 'active_contact_request_id');
}

public function user()
{
    return $this->belongsTo(User::class);
}


}
