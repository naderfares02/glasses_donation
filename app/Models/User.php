<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable 
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'city',
        'phone',
        'status',
        'suspended_at', 
        'suspended_by', 
        'suspended_reason',
        'role_changed_by', 
        'role_changed_at',

    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */ 
    protected $casts = [
        'email_verified_at' => 'datetime',
        'suspended_at' => 'datetime',
        'role_changed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

     // من قام بتعليق الحساب
    public function suspendedBy()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }


    // من قام بتغيير الدور
    public function roleChangedBy()
    {
        return $this->belongsTo(User::class, 'role_changed_by');
    }

    /**
     * اسم الراوت الذي يمثل "الرئيسية" الخاصة بهذا المستخدم حسب دوره.
     * يُستخدم بدلاً من route('dashboard') الذي لا وجود له في هذا المشروع.
     */
    public function homeRouteName(): string
    {
        return match ($this->role) {
            'donor' => 'donor.main_page',
            'recipient' => 'recipient.main_page',
            'admin', 'super_admin' => 'admin.dashboard',
            default => 'home',
        };
    }

        public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
    
        public function glasses()
    {
        return $this->hasMany(Glasses::class);
    }

    public function donationReceipts()
    {
        return $this->hasMany(DonationReceipt::class, 'donor_id');
    }

        public function receivedDonationReceipts()
    {
        return $this->hasMany(DonationReceipt::class, 'recipient_id');
    }

}
