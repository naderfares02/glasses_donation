<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;

class LegalPageController extends Controller
{
    public function show(string $key)
    {
        abort_unless(in_array($key, ['terms','privacy']), 404);

        $page = LegalPage::where('key', $key)->firstOrFail();

        return view('legal.show', compact('page'));
    }
}