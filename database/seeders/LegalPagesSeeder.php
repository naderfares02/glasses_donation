<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalPage;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        LegalPage::updateOrCreate(
            ['key' => 'terms'],
            [
                'title' => 'Terms of Use',
                'content' => '<h2>Terms of Use</h2><p>Write your terms here...</p>',
                'published_at' => now(),
            ]
        );

        LegalPage::updateOrCreate(
            ['key' => 'privacy'],
            [
                'title' => 'Privacy Policy',
                'content' => '<h2>Privacy Policy</h2><p>Write your privacy policy here...</p>',
                'published_at' => now(),
            ]
        );
    }
}