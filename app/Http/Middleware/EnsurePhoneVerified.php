<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!setting('auth.require_phone_verification', false)) {
            return $next($request);
        }

        if (auth()->check() && is_null(auth()->user()->phone_verified_at)) {
            return redirect()->route('phone.verify.notice');
        }

        return $next($request);
    }
}