<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\SettingService;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $s = app(SettingService::class);

        $s->set('site.name', 'Glasses Donation', 'string');
        $s->set('site.support_email', 'support@example.com', 'string');
        $s->set('site.maintenance', false, 'bool');

        // صفحات السياسات كنص (HTML/Markdown) - أنت اختر
        $s->set('legal.terms_title', 'Terms & Conditions', 'string');
        $s->set('legal.terms_body', "Write your terms here...", 'text');

        $s->set('legal.privacy_title', 'Privacy Policy', 'string');
        $s->set('legal.privacy_body', "Write your privacy policy here...", 'text');
    }
}