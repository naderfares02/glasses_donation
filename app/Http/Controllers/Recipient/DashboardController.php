<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $glasses = Glasses::with('primaryImage')
            ->where('status', 'available')
            ->latest()
            ->paginate(12);

         $q = trim((string) $request->query('q', ''));
        $condition = $request->query('condition', '');
        $lensType  = $request->query('lens_type', '');

        $glasses = Glasses::query()
            ->with('primaryImage')
            ->where('status', 'available');

        if ($q !== '') {
            $glasses->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                ->orWhere('lens_type', 'like', "%{$q}%")
                ->orWhere('prescription', 'like', "%{$q}%");
            });
        }

        if ($condition !== '' && in_array($condition, ['new', 'used'], true)) {
            $glasses->where('condition', $condition);
        }

        if ($lensType !== '') {
            $glasses->where('lens_type', $lensType);
        }

        $glasses = $glasses->latest()->paginate(16)->withQueryString();
        
        return view('recipient.main_page', compact('glasses'));
    }
}
