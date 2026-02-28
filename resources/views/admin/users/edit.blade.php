<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Edit User</h2>
                <p class="text-sm text-gray-500 mt-1">Update basic user information.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.show', $user->id) }}"
                    class="text-sm font-semibold px-4 py-2 rounded-xl bg-white border hover:bg-gray-50">
                    ← Back to profile
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    Users list →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- LEFT: form --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Edit card --}}
                    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                        <div class="p-5 border-b bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Basic information</p>
                            <p class="text-xs text-gray-500 mt-1">These changes affect how the user appears in the
                                system.</p>
                        </div>

                        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="p-5 space-y-5">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Name --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                                    @error('name')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                                    @error('email')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Phone --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                        placeholder="+4915123456789"
                                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200">
                                    @error('phone')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- City --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>

                                    @php
                                        $cities = ["Berlin", "Hamburg", "Munich", "Cologne", "Frankfurt", "Stuttgart", "Düsseldorf", "Dortmund", "Essen", "Leipzig", "Bremen", "Dresden", "Hanover", "Nuremberg", "Duisburg", "Bochum", "Wuppertal", "Bielefeld", "Bonn", "Münster"];
                                        $selectedCity = old('city', $user->city);
                                    @endphp

                                    <select name="city" required
                                        class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                                        <option value="" disabled {{ $selectedCity ? '' : 'selected' }}>Select city
                                        </option>

                                        @foreach($cities as $city)
                                            <option value="{{ $city }}" {{ $selectedCity === $city ? 'selected' : '' }}>
                                                {{ $city }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('city')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-3">
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                    class="px-5 py-3 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 border">
                                    Cancel
                                </a>

                                <button type="submit"
                                    class="px-5 py-3 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                    Save changes
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Danger zone: suspend / unsuspend --}}
                    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden" x-data="{ openSuspend:false }">
                        <div class="p-5 border-b bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Access control</p>
                            <p class="text-xs text-gray-500 mt-1">Suspend blocks login and usage.</p>
                        </div>

                        <div class="p-5">
                            @php
                                $isSuspended = ($user->status === 'suspended');
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        Status:
                                        <span
                                            class="ml-2 inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border
                                            {{ $isSuspended ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                                            {{ strtoupper($user->status ?? 'active') }}
                                        </span>
                                    </p>

                                    @if($isSuspended)
                                        <p class="text-xs text-gray-600 mt-1">
                                            Suspended at: <span
                                                class="font-semibold">{{ optional($user->suspended_at)->format('Y-m-d H:i') ?? '—' }}</span>
                                        </p>
                                        @if($user->suspended_reason)
                                            <p class="text-xs text-gray-600 mt-1">
                                                Reason: <span class="font-semibold">{{ $user->suspended_reason }}</span>
                                            </p>
                                        @endif
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if(!$isSuspended)
                                        <button type="button" @click="openSuspend=true"
                                            class="px-5 py-3 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                                            Suspend user
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.unsuspend', $user->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="px-5 py-3 rounded-xl text-sm font-semibold bg-green-600 hover:bg-green-700 text-white"
                                                onclick="return confirm('Unsuspend this user?');">
                                                Unsuspend
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Suspend Modal --}}
                        <div x-show="openSuspend" x-transition
                            class="fixed inset-0 z-50 flex items-center justify-center">
                            <div class="absolute inset-0 bg-black/40" @click="openSuspend=false"></div>

                            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-lg font-bold text-gray-800">Suspend user</p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            This will block the user from logging in.
                                        </p>
                                    </div>
                                    <button class="p-2 hover:bg-gray-100 rounded-lg"
                                        @click="openSuspend=false">✕</button>
                                </div>

                                <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}" class="mt-5">
                                    @csrf

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Suspension reason (required)
                                        </label>
                                        <textarea name="reason" rows="4" required
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-200"
                                            placeholder="Write a clear reason...">{{ old('reason') }}</textarea>

                                        @error('reason')
                                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mt-6 flex items-center justify-end gap-3">
                                        <button type="button" @click="openSuspend=false"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                                            Cancel
                                        </button>

                                        <button type="submit"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white"
                                            onclick="return confirm('Suspend this user now?');">
                                            Suspend
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT: user card --}}
                <div class="lg:col-span-1 space-y-6">

                    <div class="bg-white border rounded-2xl shadow-sm p-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-2xl overflow-hidden border bg-gray-100 flex items-center justify-center shrink-0">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover"
                                        alt="avatar">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center text-sm font-bold text-gray-700">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm">
                            <div class="border rounded-xl p-3 bg-gray-50">
                                <p class="text-xs text-gray-500">Role</p>
                                <p class="font-semibold text-gray-800 mt-1">{{ strtoupper($user->role) }}</p>
                            </div>

                            <div class="border rounded-xl p-3 bg-gray-50">
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="font-semibold text-gray-800 mt-1">{{ $user->phone ?? '—' }}</p>
                            </div>

                            <div class="border rounded-xl p-3 bg-gray-50">
                                <p class="text-xs text-gray-500">City</p>
                                <p class="font-semibold text-gray-800 mt-1">{{ $user->city ?? '—' }}</p>

                            </div>

                            <div class="border rounded-xl p-3 bg-gray-50">
                                <p class="text-xs text-gray-500">Joined</p>
                                <p class="font-semibold text-gray-800 mt-1">{{ $user->created_at?->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('admin.users.show', $user->id) }}"
                                class="w-full text-center px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                                View profile
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>