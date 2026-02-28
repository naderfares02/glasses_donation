<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRegistrationEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (!setting('auth.allow_registration', true)) {
            abort(403, 'Registration is currently disabled.');
        }
        return $next($request);
    }
}
