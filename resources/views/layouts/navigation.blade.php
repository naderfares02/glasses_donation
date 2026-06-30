@php
    $role = auth()->user()->role ?? null;

    $homeRoute = match ($role) {
        'donor' => 'donor.main_page',
        'recipient' => 'recipient.main_page',
        'admin', 'super_admin' => 'admin.dashboard',
        default => 'home',
    };

    // Theme حسب الدور
    $theme = match ($role) {
        'donor' => [
            'bar' => 'bg-blue-600',
            'barText' => 'text-white',
            'chip' => 'bg-white/15 text-white border-white/20',
            'linkActive' => 'text-white border-white/60',
            'link' => 'text-white/90 hover:text-white border-transparent hover:border-white/40',
            'btn' => 'bg-white text-blue-700 hover:bg-blue-50',
        ],
        'recipient' => [
            'bar' => 'bg-emerald-600',
            'barText' => 'text-white',
            'chip' => 'bg-white/15 text-white border-white/20',
            'linkActive' => 'text-white border-white/60',
            'link' => 'text-white/90 hover:text-white border-transparent hover:border-white/40',
            'btn' => 'bg-white text-emerald-700 hover:bg-emerald-50',
        ],
        'admin', 'super_admin' => [
            'bar' => 'bg-gray-900',
            'barText' => 'text-white',
            'chip' => 'bg-white/10 text-white border-white/15',
            'linkActive' => 'text-white border-white/60',
            'link' => 'text-white/80 hover:text-white border-transparent hover:border-white/40',
            'btn' => 'bg-white text-gray-900 hover:bg-gray-100',
        ],
        default => [
            'bar' => 'bg-white',
            'barText' => 'text-gray-800',
            'chip' => 'bg-gray-100 text-gray-700 border-gray-200',
            'linkActive' => 'text-blue-700 border-blue-600',
            'link' => 'text-gray-700 hover:text-gray-900 border-transparent hover:border-gray-300',
            'btn' => 'bg-blue-600 text-white hover:bg-blue-700',
        ],
    };

    // counts (الأفضل تجيبهم من Controller/Composer)
    $unreadNotif = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;

    $unreadMessages = 0;
    if (auth()->check() && in_array($role, ['donor', 'recipient'])) {
        $unreadMessages = \App\Models\Message::whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->whereHas('conversation', function ($q) use ($role) {
                if ($role === 'donor')
                    $q->where('donor_id', auth()->id());
                else
                    $q->where('recipient_id', auth()->id());
            })
            ->count();
    }

    $chatUrl = $role === 'donor'
        ? route('donor.chats.index')
        : ($role === 'recipient' ? route('recipient.chats.index') : null);
@endphp

<nav x-data="{ open: false }" class="border-b border-gray-100">
    {{-- Top bar color by role --}}
    <div class="{{ $theme['bar'] }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">

                {{-- Left: logo + role chip --}}
                <div class="flex items-center gap-4">
                    <a href="{{ route($homeRoute) }}" class="flex items-center gap-2">
                        {{-- <x-application-logo class="block h-8 w-auto fill-current {{ $theme['barText'] }}" /> --}}
                        <span class="hidden sm:inline font-semibold {{ $theme['barText'] }}">
                            {{ config('app.name') }}
                        </span>
                    </a>

                    @auth
                        <span
                            class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $theme['chip'] }}">
                            {{ strtoupper(str_replace('_', ' ', $role)) }}
                        </span>
                    @endauth
                </div>

                {{-- Center: main links (desktop) --}}
                <div class="hidden md:flex items-center gap-2">
                    @auth
                        @if($role === 'donor')
                            <a href="{{ route('donor.main_page') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('donor.main_page') ? $theme['linkActive'] : $theme['link'] }}">
                                Home
                            </a>

                            <a href="{{ route('donor.glasses.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('donor.glasses.*') ? $theme['linkActive'] : $theme['link'] }}">
                                My Glasses
                            </a>

                            {{-- <a href="{{ route('donor.chats.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('donor.chats.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Chats
                            </a> --}}

                        @elseif($role === 'recipient')
                            <a href="{{ route('recipient.main_page') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('recipient.main_page') ? $theme['linkActive'] : $theme['link'] }}">
                                Home
                            </a>

                            <a href="{{ route('recipient.donations.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('recipient.donations.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Donations
                            </a>

                            {{-- <a href="{{ route('recipient.chats.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('recipient.chats.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Chats
                            </a> --}}

                        @elseif(in_array($role, ['admin', 'super_admin']))
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('admin.dashboard') ? $theme['linkActive'] : $theme['link'] }}">
                                Dashboard
                            </a>

                            <a href="{{ route('admin.users.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('admin.users.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Users
                            </a>

                            <a href="{{ route('admin.glasses.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('admin.glasses.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Glasses
                            </a>

                            <a href="{{ route('admin.donation_requests.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('admin.donation_requests.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Donation Requests
                            </a>

                            <a href="{{ route('admin.complaints.index') }}"
                                class="px-3 py-2 text-sm font-semibold border-b-2
                                                                                                               {{ request()->routeIs('admin.complaints.*') ? $theme['linkActive'] : $theme['link'] }}">
                                Reports
                            </a>
                        @endif
                    @endauth

                    @guest
                        <a href="{{ route($homeRoute) }}"
                            class="px-3 py-2 text-sm font-semibold border-b-2
                                                                   {{ request()->routeIs($homeRoute) ? 'text-blue-700 border-blue-600' : 'text-gray-700 border-transparent hover:border-gray-300' }}">
                            Home
                        </a>
                    @endguest
                </div>

                {{-- Right: icons + profile --}}
                <div class="flex items-center gap-3">

                    @auth
                        {{-- Notifications --}}
                        <a href="{{ route('notifications.index') }}"
                            class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $role ? 'bg-white/10 hover:bg-white/15' : 'bg-gray-100 hover:bg-gray-200' }}">
                            <svg class="w-5 h-5 {{ $theme['barText'] }}" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0" />
                            </svg>

                            @if($unreadNotif > 0)
                                <span
                                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $unreadNotif > 99 ? '99+' : $unreadNotif }}
                                </span>
                            @endif
                        </a>

                        {{-- Chats for donor/recipient --}}
                        @if(in_array($role, ['donor', 'recipient']))
                            <a href="{{ $chatUrl }}"
                                class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 hover:bg-white/15">
                                <svg class="w-5 h-5 {{ $theme['barText'] }}" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 10h8M8 14h5m9-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                                @if($unreadMessages > 0)
                                    <span
                                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">
                                        {{ $unreadMessages > 99 ? '99+' : $unreadMessages }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        {{-- Dropdown --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center gap-2 px-2 py-2 rounded-xl bg-white/10 hover:bg-white/15">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                            class="w-8 h-8 rounded-full object-cover border border-white/20" alt="avatar">
                                    @else
                                        <div
                                            class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <span class="hidden sm:block text-sm font-semibold {{ $theme['barText'] }}">
                                        {{ Auth::user()->name }}
                                    </span>

                                    <svg class="hidden sm:block h-4 w-4 {{ $theme['barText'] }}"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    @endauth

                    {{-- Hamburger (mobile) --}}
                    <button @click="open = ! open"
                        class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl
                               {{ $role ? 'bg-white/10 hover:bg-white/15 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-cloak :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden bg-white border-b">
        <div class="px-4 py-3 space-y-1">
            @auth
                {{-- role-based links --}}
                @if($role === 'donor')
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('donor.main_page') }}">Home</a>
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('donor.glasses.index') }}">My Glasses</a>
                    {{-- <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('donor.chats.index') }}">Chats</a> --}}
                @elseif($role === 'recipient')
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('recipient.main_page') }}">Home</a>
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('recipient.donations.index') }}">Donations</a>
                    {{-- <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('recipient.chats.index') }}">Chats</a> --}}
                @elseif(in_array($role, ['admin', 'super_admin']))
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('admin.users.index') }}">Users</a>
                    <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                        href="{{ route('admin.glasses.index') }}">Glasses</a>
                @endif

                <div class="border-t my-2"></div>

                <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                    href="{{ route('notifications.index') }}">Notifications</a>

                <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                    href="{{ route('profile.edit') }}">Profile</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50">
                        Log Out
                    </button>
                </form>
            @endauth

            @guest
                <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                    href="{{ route($homeRoute) }}">Home</a>
                <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                    href="{{ route('login') }}">Login</a>
                <a class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50"
                    href="{{ route('register') }}">Register</a>
            @endguest
        </div>
    </div>
</nav>