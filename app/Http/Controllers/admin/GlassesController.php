<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use Illuminate\Http\Request;

class GlassesController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all'); // all | available | in_contact | reserved | pending_donation | donated

        $query = Glasses::query()
            ->with([
                'primaryImage:id,glasses_id,path',
                'user:id,name,email,avatar',
            ])
            ->latest();

        // Search (title + donor)
        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                  ->orWhereHas('user', function ($u) use ($q) {
                      $u->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                  });
            });
        }

        // Status filter
        $allowed = ['available', 'in_contact', 'reserved', 'pending_donation', 'donated'];
        if ($status !== 'all' && in_array($status, $allowed, true)) {
            $query->where('status', $status);
        }

        $glasses = $query->paginate(12)->withQueryString();

        // Counts (optional)
        $counts = Glasses::query()
            ->selectRaw("
                COUNT(*) as all_count,
                SUM(status='available') as available_count,
                SUM(status='in_contact') as in_contact_count,
                SUM(status='reserved') as reserved_count,
                SUM(status='pending_donation') as pending_donation_count,
                SUM(status='donated') as donated_count
            ")
            ->first();

        return view('admin.glasses.index', compact('glasses', 'counts', 'q', 'status'));
    }

    public function show(Glasses $glasses)
    {

        $glasses->load(['primaryImage', 'images']);

        return view('admin.glasses.show', compact('glasses'));
    }
}