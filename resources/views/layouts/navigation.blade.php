@php
    $role = auth()->user()->role ?? null;

    $homeRoute = match ($role) {
        'donor' => 'donor.main_page',
        'recipient' => 'recipient.main_page',
        'admin', 'super_admin' => 'admin.dashboard',
        default => 'home', // إن لم يكن عندك home غيّرها إلى 'welcome' أو '/'
    };
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route($homeRoute) }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        @if($role === 'donor')
                            <x-nav-link :href="route('donor.main_page')" :active="request()->routeIs('donor.main_page')">
                                Home
                            </x-nav-link>
                        @elseif($role === 'recipient')
                            <x-nav-link :href="route('recipient.main_page')"
                                :active="request()->routeIs('recipient.main_page')">
                                Home
                            </x-nav-link>
                            <x-nav-link :href="route('recipient.donations.index')"
                                :active="request()->routeIs('recipient.donations.*')">
                                Donations
                            </x-nav-link>
                        @elseif($role === 'admin' || $role === 'super_admin')
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                Dashboard
                            </x-nav-link>
                        @endif

                    @endauth


                    @guest
                        <x-nav-link :href="route($homeRoute)" :active="request()->routeIs($homeRoute)">
                            Home
                        </x-nav-link>
                    @endguest

                </div>
            </div>

            <!-- Right Side -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                @auth
                    @php
                        // Unread notifications count
                        $unreadNotif = auth()->user()->unreadNotifications()->count();

                        // Unread messages count (only donor/recipient)
                        $unreadMessages = 0;
                        if (in_array($role, ['donor', 'recipient'])) {
                            $unreadMessages = \App\Models\Message::whereNull('read_at')
                                ->where('sender_id', '!=', auth()->id())
                                ->whereHas('conversation', function ($q) use ($role) {
                                    if ($role === 'donor') {
                                        $q->where('donor_id', auth()->id());
                                    } else {
                                        $q->where('recipient_id', auth()->id());
                                    }
                                })
                                ->count();
                        }

                        $chatUrl = $role === 'donor'
                            ? route('donor.chats.index')
                            : route('recipient.chats.index');
                    @endphp

                    <!-- Icons -->
                    <div class="flex items-center gap-4 mr-4">

                        <!-- Notifications -->
                        <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center">
                            <svg class="w-6 h-6 text-gray-700 hover:text-gray-900" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0" />
                            </svg>

                            @if($unreadNotif > 0)
                                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-600 rounded-full"></span>
                            @endif
                        </a>

                        <!-- Chats (only donor/recipient) -->
                        @if(in_array($role, ['donor', 'recipient']))
                            <a href="{{ $chatUrl }}" class="relative inline-flex items-center">
                                <svg class="w-6 h-6 text-gray-700 hover:text-gray-900" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 10h8M8 14h5m9-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                                @if($unreadMessages > 0)
                                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-600 rounded-full"></span>
                                @endif
                            </a>
                        @endif
                    </div>
                @endauth

                <!-- Settings Dropdown -->
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center gap-2">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                            class="w-8 h-8 rounded-full object-cover border" alt="avatar">
                                    @else
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-700">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>{{ Auth::user()->name }}</div>
                                </div>


                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
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
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if($role === 'donor')
                    <x-responsive-nav-link :href="route('donor.main_page')" :active="request()->routeIs('donor.main_page')">
                        Donor Home
                    </x-responsive-nav-link>
                @elseif($role === 'recipient')
                    <x-responsive-nav-link :href="route('recipient.main_page')"
                        :active="request()->routeIs('recipient.main_page')">
                        Recipient Home
                    </x-responsive-nav-link>
                @elseif($role === 'admin' || $role === 'super_admin')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                        Admin Dashboard
                    </x-responsive-nav-link>
                @endif

                {{-- Mobile icons links --}}
                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    Notifications
                </x-responsive-nav-link>

                @if(in_array($role, ['donor', 'recipient']))
                    <x-responsive-nav-link :href="$role === 'donor' ? route('donor.chats.index') : route('recipient.chats.index')"
                        :active="request()->routeIs($role . '.chats.*')">
                        Chats
                    </x-responsive-nav-link>
                @endif
            @endauth

            @guest
                <x-responsive-nav-link :href="route($homeRoute ?? 'home')">
                    Home
                </x-responsive-nav-link>
            @endguest
        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>