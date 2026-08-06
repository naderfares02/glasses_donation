<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use Illuminate\Http\Request;

class GlassesController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $query = Glasses::query()
            ->with(['primaryImage', 'donor:id,name,email']);

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $query->orderByRaw("
            CASE status
                WHEN 'pending_donation' THEN 1
                WHEN 'in_contact' THEN 2
                WHEN 'reserved' THEN 3
                WHEN 'available' THEN 4
                WHEN 'donated' THEN 5
                ELSE 6
            END
        ");
        $query->orderByDesc('created_at');

        $glasses = $query->paginate(10)->withQueryString();

        return view('admin.glasses.index', compact('glasses'));
    }

    public function show(Glasses $glasses)
    {
        $this->ensureAdmin();

        $glasses->load(['primaryImage', 'images', 'donor:id,name,email,phone']);

        return view('admin.glasses.show', compact('glasses'));
    }
}