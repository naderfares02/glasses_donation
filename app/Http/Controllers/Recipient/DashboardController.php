<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $glasses = Glasses::with('primaryImage')
            ->where('status', 'available')
            ->latest()
            ->paginate(12);

        return view('recipient.main_page', compact('glasses'));
    }
}
