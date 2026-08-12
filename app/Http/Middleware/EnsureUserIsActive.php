<?php

namespace App\Http\Middleware;

use Closure;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
public function handle($request, Closure $next)
{
    $user = $request->user();

    if ($user && $user->status === 'suspended') {

        session([
            'suspended_user' => [
                'reason' => $user->suspended_reason,
                'by' => optional($user->suspendedBy)->name,
                'at' => $user->suspended_at,
            ]
        ]);

        Auth::logout();

        return redirect()->route('suspended');
    }

    return $next($request);
}
}