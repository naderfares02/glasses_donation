<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalPagesController extends Controller
{
    public function index()
    {
        $pages = LegalPage::with('editor:id,name')->orderBy('key')->get();
        return view('admin.legal.index', compact('pages'));
    }

    public function edit(LegalPage $page)
    {
        return view('admin.legal.edit', compact('page'));
    }

        public function update(Request $request, LegalPage $page)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'publish' => ['nullable','boolean'],
        ]);

        // احفظ النسخة الحالية كأرشيف قبل ما نكتب فوقها
        \App\Models\LegalPageRevision::create([
            'legal_page_id' => $page->id,
            'title'         => $page->title,
            'content'       => $page->content,
            'updated_by'    => $page->updated_by,
            'created_at'    => $page->updated_at ?? now(),
        ]);

        $page->update([
            'title' => $data['title'],
            'content' => clean($data['content']), // مع إصلاح XSS من الرد السابق
            'updated_by' => auth()->id(),
            'published_at' => ($request->boolean('publish')) ? now() : $page->published_at,
        ]);

        return back()->with('success', 'Page updated.');
    }
}