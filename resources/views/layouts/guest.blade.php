<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col justify-center px-4 py-10">

        {{-- Logo --}}
        {{-- <div class="flex justify-center mb-6">
            <a href="/" class="inline-flex items-center gap-3">
                <x-application-logo class="w-14 h-14 fill-current text-gray-600" />
                <span class="text-xl font-bold text-gray-800">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div> --}}

        {{-- Card --}}
        <div class="w-full max-w-3xl mx-auto bg-white border shadow-sm rounded-2xl overflow-hidden">
            <div class="p-6 sm:p-10">
                {{ $slot }}
            </div>
        </div>

    </div>
</body>

</html>