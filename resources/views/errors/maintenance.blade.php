<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-xl bg-white border rounded-3xl shadow-sm overflow-hidden">
            <div class="p-8 border-b bg-gray-50">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                    MAINTENANCE MODE
                </div>
                <h1 class="mt-4 text-2xl font-extrabold text-gray-900">We’ll be back soon</h1>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                    The platform is temporarily unavailable for maintenance. Please try again later.
                </p>
            </div>

            <div class="p-8 space-y-4">
                <div class="p-4 rounded-2xl border bg-gray-50">
                    <p class="text-sm font-semibold text-gray-800">What you can do now</p>
                    <ul class="mt-2 text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Refresh the page after a few minutes</li>
                        <li>Check back later</li>
                        <li>If you need help, contact support</li>
                    </ul>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <a href="{{ url('/') }}"
                        class="px-5 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        Go to home
                    </a>

                    <span class="text-xs text-gray-500">
                        Error 503
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>