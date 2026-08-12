@php
    $role = auth()->user()->role ?? null;
    $homeRoute = match ($role) {
        'donor' => 'donor.main_page',
        'recipient' => 'recipient.main_page',
        'admin', 'super_admin' => 'admin.dashboard',
        default => 'home',
    };
@endphp


<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">
    <div class="bg-white border-b">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
            <a href="{{ route($homeRoute) }}" class="flex items-center"> <img
                    src="{{ asset('images/givesight-logo-transparent.png') }}" alt="GiveSight"
                    class="block h-10 w-auto">
            </a>
            @auth
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                    ← Back
                </a>
            @endauth

        </div>
    </div>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-xs text-gray-500 mb-3 text-end">
                Last updated: {{ $page->updated_at?->format('Y-m-d H:i') ?? '—' }}
            </p>
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-10 legal-content">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</body>

</html>