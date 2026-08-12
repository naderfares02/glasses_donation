<?php

// app/Policies/GlassesPolicy.php
namespace App\Policies;

use App\Models\Glasses;
use App\Models\User;

class GlassesPolicy
{

    public function view(User $user, Glasses $glasses): bool
    {
        return $glasses->user_id === $user->id;
    }

    public function update(User $user, Glasses $glasses): bool
    {
        return $glasses->user_id === $user->id;
    }

    public function delete(User $user, Glasses $glasses): bool
    {
        return $glasses->user_id === $user->id;
    }
}
