<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{

    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'role' => ['required', 'in:donor,recipient'],
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        'phone' => [
            'required',
            'regex:/^\+49[0-9]{9,12}$/',
            'unique:users,phone',
        ],
        'phone.regex' => 'Phone must start with +49 and contain only numbers after it.',
        'city' => ['required', 'string', 'max:255'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'terms' => ['accepted'],
    ]);

    $user = User::create([
        'role' => $request->role,
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'city'  => $request->city,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

            return match ($user->role) {
            'donor' => redirect()->route('donor.main_page'),
            'recipient' => redirect()->route('recipient.main_page'),
            default => redirect('/'),
        };
}
}
