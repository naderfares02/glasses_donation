<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Models\User;
use Illuminate\Http\Request;

class ControlController extends Controller
{
    public function index(Request $request)
{
    $isSuperAdmin = auth()->user()->role === 'super_admin';

    $legal = LegalPage::query()
        ->whereIn('key', ['terms', 'privacy'])
        ->orderByRaw("CASE `key` WHEN 'terms' THEN 1 WHEN 'privacy' THEN 2 ELSE 3 END")
        ->get()
        ->keyBy('key');

    $isDown = (bool) setting('site.maintenance', false);

    $counts = [
        'admins'            => User::whereIn('role', ['admin','super_admin'])->count(),
        'pending_donations' => \App\Models\DonationRequest::where('status', 'pending')->count(),
        'suspended'         => User::where('status', 'suspended')->count(), // ✅ حسب DB عندك
    ];

    return view('admin.control.index', compact('isSuperAdmin', 'legal', 'counts', 'isDown'));
}
}