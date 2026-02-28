<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Policies\ComplaintPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Complaint::class => ComplaintPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}