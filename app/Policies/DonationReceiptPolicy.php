<?php

// app/Policies/DonationReceiptPolicy.php
namespace App\Policies;

use App\Models\DonationReceipt;
use App\Models\User;

class DonationReceiptPolicy
{
    public function view(User $user, DonationReceipt $receipt): bool
    {
        // admin/super_admin يشوفوا الكل
        if (in_array($user->role, ['admin','super_admin'], true)) return true;

        // المتبرع يشوف إيصالاته فقط
        if ($user->role === 'donor' && $receipt->donor_id === $user->id) return true;

        return false;
    }

    public function download(User $user, DonationReceipt $receipt): bool
    {
        return $this->view($user, $receipt);
    }
}