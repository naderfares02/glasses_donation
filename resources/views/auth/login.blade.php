<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            {{-- Logo / Title --}}
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center text-xl">
                    <a href="#" class="flex items-center"> <img
                            src="{{ asset('images/givesight-full-transparent.png') }}" alt="GiveSight"
                            class="block h-20 w-auto">
                    </a>
                </div>
                <h1 class="mt-4 text-2xl font-extrabold text-gray-900">Welcome back</h1>
                <p class="mt-1 text-sm text-gray-600">Log in to continue.</p>
            </div>

            {{-- Card --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email</label>
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus
                            autocomplete="username"
                            class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#35A899]/30 focus:border-[#35A899]">
                        @error('email')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Password</label>
                        <input name="password" type="password" required autocomplete="current-password"
                            class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#35A899]/30 focus:border-[#35A899]">
                        @error('password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="remember"
                                class="rounded border-gray-300 text-[#35A899] focus:ring-[#35A899]/30">
                            Remember me
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-sm font-semibold text-[#35A899] hover:text-[#2c8f82]">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="w-full px-5 py-3 rounded-xl bg-[#35A899] hover:bg-[#2c8f82] text-white font-semibold">
                        Log in
                    </button>

                    {{-- Register link --}}
                    <div class="text-center text-sm text-gray-600">
                        Don’t have an account?
                        <a href="{{ route('register') }}" class="font-semibold text-[#35A899] hover:text-[#2c8f82]">
                            Create one
                        </a>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <p class="text-center text-xs text-gray-500 mt-6">
                By continuing you agree to the platform rules and privacy policy.
            </p>
        </div>
    </div>
</body>

</html>