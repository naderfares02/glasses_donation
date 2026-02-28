<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
        ['email' => 'superadmin@example.com'],
        [
            'name' => 'nader',
            'password' => Hash::make('123123123'),
            'role' => 'super_admin',
            'phone' => '+4915123956786',
            'city' => 'Leipzig',
            'email_verified_at' => now(),
        ]
    );
    }
}
