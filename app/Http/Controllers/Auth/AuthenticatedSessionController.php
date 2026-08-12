<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }


    public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = $request->user();

    $fallback = match ($user->role) {
        'donor' => route('donor.main_page'),
        'recipient' => route('recipient.main_page'),
        'admin', 'super_admin' => route('admin.dashboard'),
        default => url('/'),
    };

    if (session('url.intended') === url('/dashboard')) {
        session()->forget('url.intended');
    }

    return redirect()->intended($fallback);
}

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
