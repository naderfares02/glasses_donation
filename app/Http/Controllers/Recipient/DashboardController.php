<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $condition = $request->query('condition', '');
        $lensType  = $request->query('lens_type', '');

        $glasses = Glasses::query()
            ->with([
                'primaryImage',
                'user'
            ])
            ->where('status', 'available');

        if ($q !== '') {

    $glasses->where(function ($query) use ($q) {

        $query->where('title', 'like', "%{$q}%")
            ->orWhere('serial_number', 'like', "%{$q}%")
            ->orWhere('brand', 'like', "%{$q}%")
            ->orWhereHas('user', function ($userQuery) use ($q) {
                $userQuery->where('name', 'like', "%{$q}%");
            });

    });

}


        if (
            $condition !== '' &&
            in_array($condition, ['new', 'used'], true)
        ) {

            $glasses->where('condition', $condition);

        }

        
        if ($lensType !== '' && in_array($lensType, \App\Models\Glasses::LENS_TYPES, true)) {
            $glasses->where('lens_type', $lensType);
        }


        $glasses = $glasses
            ->latest()
            ->paginate(16)
            ->withQueryString();


        return view('recipient.main_page', compact('glasses'));
    }
}