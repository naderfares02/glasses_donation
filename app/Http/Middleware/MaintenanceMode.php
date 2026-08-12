<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        $enabled = (bool) setting('site.maintenance', false);

        if (!$enabled) {
            return $next($request);
        }

        if ($request->is('storage/*') || $request->is('build/*')) {
            return $next($request);
        }

        if ($request->is('login') || $request->is('logout') || $request->is('password/*')) {
            return $next($request);
        }

        if (Auth::check()) {

           if (Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin'])) {
                return $next($request);
            }if (Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin'])) {
                return $next($request);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->view('errors.maintenance', [], 503);
        }

        return response()->view('errors.maintenance', [], 503);
    }
}