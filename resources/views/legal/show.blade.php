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
            <div>
                <p class="text-sm font-semibold text-blue-700">Legal</p>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $page->title }}</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Last updated: {{ $page->updated_at?->format('Y-m-d H:i') ?? '—' }}
                </p>
            </div>
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
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-10 prose max-w-none">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</body>

</html>