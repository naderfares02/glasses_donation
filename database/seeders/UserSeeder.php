<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Donor
        User::create([
            'name' => 'Donor User',
            'email' => 'donor@test.com',
            'password' => Hash::make('123123123'),
            'role' => 'donor',
        ]);

        // Recipient
        User::create([
            'name' => 'Recipient User',
            'email' => 'recipient@test.com',
            'password' => Hash::make('123123123'),
            'role' => 'recipient',
        ]);

        // manager
        User::create([
            'name' => 'Manager User',
            'email' => 'admin@test.com',
            'password' => Hash::make('123123123'),
            'role' => 'admin',
        ]);
    }
}