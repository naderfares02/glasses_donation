<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">System Control</h2>
                <p class="text-sm text-gray-500 mt-1">Manage platform status, core settings, and platform rules.</p>
            </div>

            <a href="{{ route('admin.control') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-2xl">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Top cards --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- System Status --}}
                <div class="bg-white border rounded-3xl shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">System Status</p>
                            <p class="text-xs text-gray-500 mt-1">Current platform availability.</p>
                        </div>

                        <span
                            class="text-xs font-semibold px-3 py-1 rounded-full border
                            {{ $isDown ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                            {{ $isDown ? 'MAINTENANCE' : 'LIVE' }}
                        </span>
                    </div>

                    <div class="mt-5">
                        <p class="text-sm text-gray-700">
                            {{ $isDown ? 'The site is currently closed for non-admin users.' : 'The site is available for all users.' }}
                        </p>
                    </div>

                    <div class="mt-5">
                        @if(!$isDown)
                            <button type="button" onclick="openMaintenanceModal()"
                                class="w-full px-4 py-3 rounded-2xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                                Enable Maintenance
                            </button>
                        @else
                            <form method="POST" action="{{ route('admin.settings.maintenance.off') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-3 rounded-2xl text-sm font-semibold bg-green-600 hover:bg-green-700 text-white">
                                    Disable Maintenance
                                </button>
                            </form>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Tip: Maintenance is enforced via middleware + setting flag.
                    </p>
                </div>

                {{-- Quick Tools --}}
                <div class="bg-white border rounded-3xl shadow-sm p-6">
                    <p class="text-sm font-semibold text-gray-800">Quick Tools</p>
                    <p class="text-xs text-gray-500 mt-1">Optional admin actions.</p>

                    <div class="mt-5 space-y-3">
                        <form method="POST" action="{{ route('admin.system.cache.clear') }}">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-3 rounded-2xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800 text-left">
                                Clear Cache
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.system.optimize') }}">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-3 rounded-2xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800 text-left">
                                Optimize
                            </button>
                        </form>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Keep only what you use.
                    </p>
                </div>


            </div>

            {{-- ONE FORM (Core + Rules) --}}
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Core Settings --}}
                    <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                        <div class="p-5 border-b bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Core Settings</p>
                            <p class="text-xs text-gray-500 mt-1">Basic configuration for the platform.</p>
                        </div>

                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Site name</label>
                                <input name="site_name" value="{{ old('site_name', $site_name) }}" required
                                    class="w-full border rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                                @error('site_name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Support email</label>
                                <input name="support_email" value="{{ old('support_email', $support_email) }}"
                                    class="w-full border rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                                @error('support_email') <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Platform Rules --}}
                    <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                        <div class="p-5 border-b bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Platform Rules</p>
                            <p class="text-xs text-gray-500 mt-1">Registration, verification, donation workflow.</p>
                        </div>

                        <div class="p-5 space-y-3">

                            <input type="hidden" name="allow_registration" value="0">
                            <input type="hidden" name="require_phone_verification" value="0">
                            <input type="hidden" name="require_admin_approval_for_donated" value="0">

                            @php
                                $toggle = function ($name, $title, $desc, $checked) {
                                    $id = 't_' . $name;
                                    return '
                                                                                                                                                                                                                                                                                                                                                                    <label for="' . $id . '" class="flex items-start justify-between gap-4 p-4 rounded-2xl border bg-white hover:bg-gray-50 transition">
                                                                                                                                                                                                                                                                                                                                                                        <div>
                                                                                                                                                                                                                                                                                                                                                                            <p class="text-sm font-semibold text-gray-800">' . $title . '</p>
                                                                                                                                                                                                                                                                                                                                                                            <p class="text-xs text-gray-500 mt-1">' . $desc . '</p>
                                                                                                                                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                                                                                                                                        <div class="mt-1">
                                                                                                                                                                                                                                                                                                                                                                            <input id="' . $id . '" type="checkbox" name="' . $name . '" value="1"
                                                                                                                                                                                                                                                                                                                                                                                class="peer sr-only" ' . ($checked ? 'checked' : '') . '>

                                                                                                                                                                                                                                                                                                                                                                            <div class="w-12 h-7 rounded-full border bg-gray-200 peer-checked:bg-blue-600 peer-checked:border-blue-600 transition relative">
                                                                                                                                                                                                                                                                                                                                                                                <div class="w-6 h-6 bg-white rounded-full shadow-sm absolute top-0.5 left-0.5 peer-checked:translate-x-5 transition"></div>
                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                    </label>';
                                };
                            @endphp

                            {!! $toggle(
    'allow_registration',
    'Allow registration',
    'Enable/disable new account creation.',
    (bool) old('allow_registration', $allow_registration ?? true)
) !!}

                            {!! $toggle(
    'require_phone_verification',
    'Require phone verification',
    'Users must verify phone before using the platform.',
    (bool) old('require_phone_verification', $require_phone_verification ?? false)
) !!}

                            {!! $toggle(
    'require_admin_approval_for_donated',
    'Require admin approval for “donated” status',
    'Glasses become DONATED only after admin approval.',
    (bool) old('require_admin_approval_for_donated', $require_admin_approval_for_donated ?? true)
) !!}

                            @error('allow_registration') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('require_phone_verification') <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('require_admin_approval_for_donated') <p class="text-sm text-red-600">{{ $message }}
                            </p> @enderror

                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="px-7 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                        Save Settings
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- Maintenance confirmation modal --}}
    <div id="maintenanceModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

        <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-800">
                Enable maintenance mode?
            </h3>

            <p class="text-sm text-gray-600 mt-2">
                The site will become unavailable for all users except administrators.
                Are you sure you want to continue?
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeMaintenanceModal()"
                    class="px-4 py-2 rounded-xl border bg-gray-100 hover:bg-gray-200 text-sm font-semibold">
                    Cancel
                </button>

                <form method="POST" action="{{ route('admin.settings.maintenance.on') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">
                        Yes, enable
                    </button>
                </form>
            </div>
        </div>
    </div>


    <script>
        function openMaintenanceModal() {
            document.getElementById('maintenanceModal').classList.remove('hidden');
            document.getElementById('maintenanceModal').classList.add('flex');
        }

        function closeMaintenanceModal() {
            document.getElementById('maintenanceModal').classList.remove('flex');
            document.getElementById('maintenanceModal').classList.add('hidden');
        }
    </script>
</x-app-layout>