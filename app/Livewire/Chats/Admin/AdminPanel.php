<?php
class AdminPanel extends \Livewire\Component
{
    public string $tab = 'dashboard';

    public function setTab(string $tab): void
    {
        $allowed = ['dashboard','users','glasses','donation_requests','reports'];
        if (!in_array($tab, $allowed, true)) return;

        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.admin-panel');
    }
}