<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemSettingsController extends Controller
{
public function index(SettingService $settings)
{
    return view('admin.settings.index', [
        'site_name' => $settings->get('site.name', config('app.name')),
        'support_email' => $settings->get('site.support_email', ''),
        'isDown' => (bool) $settings->get('site.maintenance', false),

        'allow_registration' => (bool) $settings->get('auth.allow_registration', true),
        'require_phone_verification' => (bool) $settings->get('auth.require_phone_verification', false),
        'require_admin_approval_for_donated' => (bool) $settings->get('donations.require_admin_approval_for_donated', true),
    ]);
}

    public function update(Request $request, SettingService $settings)
{
    $data = $request->validate([
        'site_name' => ['required','string','max:100'],
        'support_email' => ['nullable','email','max:255'],

        'allow_registration' => ['required','boolean'],
        'require_phone_verification' => ['required','boolean'],
        'require_admin_approval_for_donated' => ['required','boolean'],
    ]);

    $settings->set('site.name', $data['site_name'], 'string');
    $settings->set('site.support_email', $data['support_email'] ?? '', 'string');

    $settings->set('auth.allow_registration', (bool)$data['allow_registration'], 'bool');
    $settings->set('auth.require_phone_verification', (bool)$data['require_phone_verification'], 'bool');
    $settings->set('donations.require_admin_approval_for_donated', (bool)$data['require_admin_approval_for_donated'], 'bool');

    return back()->with('success', 'Settings updated successfully.');
}
    public function enableMaintenance(SettingService $settings)
    {
        $settings->set('site.maintenance', true, 'bool');

        return back()->with('success', 'Maintenance enabled.');
    }

    public function disableMaintenance(SettingService $settings)
    {
        $settings->set('site.maintenance', false, 'bool');
        return back()->with('success', 'Maintenance disabled.');
    }

    public function clearCache()
    {
        Artisan::call('optimize:clear');
        return back()->with('success', 'System cache cleared successfully.');
    }

    public function optimize()
    {
        Artisan::call('optimize');
        return back()->with('success', 'System optimized successfully.');
    }
}