<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Glasses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'serial_number',
        
        // Basic
        'title',
        'description',
        'condition',
        'status',

        // Listing details
        'brand',
        'lens_type',
        'vision_type',

        // Prescription
        'sph',
        'cyl',
        'axis',
        'pd',
        'prescription_note',

        // Frame details
        'frame_size',
        'frame_color',
        'age_group',
        'gender',

        // Delivery/contact
        'pickup_city',
        'contact_method',

        'active_contact_request_id',
    ];

    /**
     * Optional casts (لو عندك أعمدة تاريخ/أرقام... عدّل حسب جدولك)
     */
    protected $casts = [
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];


    public const CONDITIONS = ['new', 'used'];

    public const STATUSES = [
        'available',
        'reserved',
        'in_contact',
        'pending_donation',
        'donated',
    ];

    public const LENS_TYPES = [
        'single_vision',
        'bifocal',
        'progressive',
        'reading',
        'non_prescription',
        'other',
    ];

    public const VISION_TYPES = ['distance', 'near', 'both', 'unknown'];

    public const FRAME_SIZES = ['small', 'medium', 'large', 'unknown'];

    public const AGE_GROUPS = ['adult', 'teen', 'kids', 'unknown'];

    public const GENDERS = ['male', 'female', 'unisex', 'unknown'];

    public const CONTACT_METHODS = ['chat_only', 'phone', 'both'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GlassesImage::class, 'glasses_id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(GlassesImage::class, 'glasses_id')
            ->where('is_primary', true);
    }

    public function contactRequests(): HasMany
    {
        return $this->hasMany(ContactRequest::class, 'glasses_id');
    }

    public function activeContactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class, 'active_contact_request_id');
    }

    public function conversations()
    {
        return $this->hasMany(\App\Models\Conversation::class, 'glasses_id');
    }

        public function receipt()
    {
        return $this->hasOne(DonationReceipt::class);
    }
}
