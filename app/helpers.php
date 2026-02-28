<?php

use App\Services\SettingService;

if (!function_exists('setting')) {
    function setting(string $key, $default = null) {
        return app(SettingService::class)->get($key, $default);
    }
}