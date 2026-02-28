<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Glasses;

class GlassesController extends Controller
{
    public function show(Glasses $glasses)
    {
        // فقط النظارات المتاحة للمستفيد

        $glasses->load(['primaryImage', 'images']);

        return view('recipient.glasses_show', compact('glasses'));
    }
}
