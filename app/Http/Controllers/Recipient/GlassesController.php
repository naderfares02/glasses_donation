<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Glasses;

class GlassesController extends Controller
{
    public function show(Glasses $glasses)
    {
        // فقط النظارات المتاحة للمستفيد
        if ($glasses->status == 'in_contact' || $glasses->status == 'reserved') {
            abort(404);
        }

        $glasses->load(['primaryImage', 'images']);

        return view('recipient.glasses_show', compact('glasses'));
    }
}
