<?php

use Livewire\Component;
use Livewire\Attributes\Url;

new class extends Component {
    #[Url(as: 'tab', except: 'dashboard')]
    public string $tab = 'dashboard';

    public function mount(): void
    {
        $this->tab = $this->normalizeTab($this->tab);
    }

    public function go(string $tab): void
    {
        $this->tab = $this->normalizeTab($tab);
        $this->dispatch('admin-tab-changed', tab: $this->tab); 
    }

    private function normalizeTab(string $tab): string
    {
        $allowed = ['dashboard', 'users', 'glasses', 'donation_requests', 'reports'];
        return in_array($tab, $allowed, true) ? $tab : 'dashboard';
    }
};
?>

<div class="relative z-50 hidden md:flex items-center gap-2 border-b">
    <button type="button" wire:click="go('dashboard')"
        class="px-3 py-3 text-sm font-semibold border-b-2
                    {{ $tab === 'dashboard' ? 'text-blue-600 border-blue-600' : 'text-gray-700 border-transparent hover:text-gray-900' }}">
        Dashboard
    </button>

    <button type="button" wire:click="go('users')"
        class="px-3 py-3 text-sm font-semibold border-b-2
                    {{ $tab === 'users' ? 'text-blue-600 border-blue-600' : 'text-gray-700 border-transparent hover:text-gray-900' }}">
        Users
    </button>

    <button type="button" wire:click="go('glasses')"
        class="px-3 py-3 text-sm font-semibold border-b-2
                    {{ $tab === 'glasses' ? 'text-blue-600 border-blue-600' : 'text-gray-700 border-transparent hover:text-gray-900' }}">
        Glasses
    </button>

    <button type="button" wire:click="go('donation_requests')"
        class="px-3 py-3 text-sm font-semibold border-b-2
                    {{ $tab === 'donation_requests' ? 'text-blue-600 border-blue-600' : 'text-gray-700 border-transparent hover:text-gray-900' }}">
        Donation Requests
    </button>

    <button type="button" wire:click="go('reports')"
        class="px-3 py-3 text-sm font-semibold border-b-2
                    {{ $tab === 'reports' ? 'text-blue-600 border-blue-600' : 'text-gray-700 border-transparent hover:text-gray-900' }}">
        Reports
    </button>
</div>