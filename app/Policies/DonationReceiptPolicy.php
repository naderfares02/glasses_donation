<?php

// app/Policies/DonationReceiptPolicy.php
namespace App\Policies;

use App\Models\DonationReceipt;
use App\Models\User;

class DonationReceiptPolicy
{
    public function view(User $user, DonationReceipt $receipt): bool
    {
        if (in_array($user->role, ['admin','super_admin'], true)) return true;

        if ($user->role === 'donor' && $receipt->donor_id === $user->id) return true;

        return false;
    }

    public function download(User $user, DonationReceipt $receipt): bool
    {
        return $this->view($user, $receipt);
    }
}