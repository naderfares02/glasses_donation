<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Suspended</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl">
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-8 sm:p-10 bg-gradient-to-br from-red-50 to-white border-b">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-red-700">Access blocked</p>
                            <h1 class="mt-2 text-3xl font-extrabold text-gray-900">
                                Your account is suspended
                            </h1>
                            <p class="mt-3 text-gray-600">
                                You can’t access the platform right now. If you believe this is a mistake, please
                                contact support.
                            </p>
                        </div>

                        <div
                            class="shrink-0 w-12 h-12 rounded-2xl bg-red-100 border border-red-200 flex items-center justify-center">
                            <span class="text-red-700 text-xl">!</span>
                        </div>
                    </div>
                </div>

                <div class="p-8 sm:p-10 space-y-6">
                    @php
                        $user = auth()->user();
                        $reason = $user?->suspended_reason;
                        $by = $user?->suspendedBy?->name;
                        $at = $user?->suspended_at;
                        $data = session('suspended_user');
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl border bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500">Status</p>
                            <p class="mt-1 text-sm font-bold text-red-700">SUSPENDED</p>
                        </div>

                        <div class="p-4 rounded-2xl border bg-gray-50">
                            <p class="text-sm text-gray-700">
                                Suspended at: <br>
                                <span class="font-semibold">
                                    {{ $data['at'] ? \Carbon\Carbon::parse($data['at'])->format('Y-m-d H:i') : '—' }}
                                </span>
                            </p>

                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border bg-white">
                        <p class="text-sm font-semibold text-gray-800">Reason</p>

                        @if($reason)
                            <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $reason }}</p>
                        @else
                            <div class="mt-2 p-4 rounded-xl bg-amber-50 border border-amber-200">
                                <p class="text-sm font-semibold text-amber-800">No reason provided</p>
                                <p class="text-sm text-amber-700 mt-1">Please contact support for more details.</p>
                            </div>
                        @endif

                        @if($by)
                            <p class="mt-3 text-xs text-gray-500">Suspended by: <span class="font-semibold">{{ $by }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 justify-end">
                        <a href="{{ url('/') }}"
                            class="inline-flex justify-center px-5 py-3 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                            Back to home
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full inline-flex justify-center px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                                Logout
                            </button>
                        </form>
                    </div>

                    <div class="pt-2 text-xs text-gray-500">
                        If you need help, contact the admin or support team.
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>