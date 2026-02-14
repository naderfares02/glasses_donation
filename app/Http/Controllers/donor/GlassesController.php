<?php

namespace App\Http\Controllers\donor;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use App\Models\GlassesImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GlassesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $glasses = Glasses::where('user_id', auth()->id())
        ->orderByRaw("
            FIELD(status, 'pending_donation', 'in_contact', 'available', 'donated')
        ")
        ->latest() // ترتيب داخلي داخل كل حالة حسب التاريخ
        ->paginate(10);

    return view('donor.index_glasses', compact('glasses'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('donor.add_glass');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1) Validation
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lens_type' => ['nullable', 'string', 'max:255'],
            'prescription' => ['nullable', 'string', 'max:255'],
            'condition' => ['required', 'in:new,used'],
            'main_image' => 'required|image|max:2048',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // 2) إضافة بيانات النظام (لا تجعلها تأتي من الفورم)
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'available';

        // 3) Create
        $glasses = Glasses::create($validated);

        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('glasses', 'public');

            GlassesImage::create([
                'glasses_id' => $glasses->id,
                'path' => $path,
                'is_primary' => true,
            ]);
        }

        // حفظ الصور الإضافية (حد أقصى 3)
        if ($request->hasFile('images')) {
            $images = $request->file('images');

            foreach (array_slice($images, 0, 3) as $image) {
                $path = $image->store('glasses', 'public');

                GlassesImage::create([
                    'glasses_id' => $glasses->id,
                    'path' => $path,
                    'is_primary' => false,
                ]);
            }
        }

        // 4) Redirect + flash message
        return redirect()
            ->route('donor.glasses.index')
            ->with('success', 'Glasses added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Glasses $glasses)
{
    // أمان: فقط صاحب النظارة
    if ($glasses->user_id !== auth()->id()) {
        abort(403);
    }

    $glasses->load(['primaryImage', 'images']);

    return view('donor.glasses_show', compact('glasses'));
}


    /**
     * Show the form for editing the specified resource.
     */
   public function edit($id)
{
    $glasses = Glasses::with(['images', 'primaryImage'])
        ->where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail(); // إذا ليست له -> 404

    return view('donor.edit_glass', compact('glasses'));
}

    /**
     * Update the specified resource in storage.
     */

        public function update(Request $request, Glasses $glasses)
{

//     if ($glasses->user_id !== auth()->id()) {
//     abort(403);
// }

    // تحميل الصور
    $glasses->load('images');

    // Validation
    $validated = $request->validate([
        'title'        => ['required', 'string', 'max:255'],
        'description'  => ['nullable', 'string', 'max:2000'],
        'lens_type'    => ['nullable', 'string', 'max:255'],
        'prescription' => ['nullable', 'string', 'max:255'],
        'condition'    => ['required', 'in:new,used'],

        'main_image'   => ['nullable', 'image', 'max:2048'],
        'images.*'     => ['nullable', 'image', 'max:2048'],
    ]);

    // تحديث بيانات النظارة
    $glasses->update([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'lens_type' => $validated['lens_type'] ?? null,
        'prescription' => $validated['prescription'] ?? null,
        'condition' => $validated['condition'],
    ]);

    // 1) استبدال الصورة الرئيسية إن وُجدت
    if ($request->hasFile('main_image')) {
        // احذف القديمة (إن وجدت)
        $oldPrimary = $glasses->images->firstWhere('is_primary', true);
        if ($oldPrimary) {
            Storage::disk('public')->delete($oldPrimary->path);
            $oldPrimary->delete();
        }

        // خزّن الجديدة
        $path = $request->file('main_image')->store('glasses', 'public');

        $glasses->images()->create([
            'path' => $path,
            'is_primary' => true,
        ]);
    }

    // 2) إضافة صور إضافية جديدة حتى الحد الأقصى 3
    if ($request->hasFile('images')) {
        $currentAdditionalCount = $glasses->images->where('is_primary', false)->count();
        $remaining = max(0, 3 - $currentAdditionalCount);

        if ($remaining > 0) {
            $uploads = array_slice($request->file('images'), 0, $remaining);

            foreach ($uploads as $image) {
                $path = $image->store('glasses', 'public');
                $glasses->images()->create([
                    'path' => $path,
                    'is_primary' => false,
                ]);
            }
        }
    }

    return redirect()
        ->route('donor.glasses.edit', $glasses->id)
        ->with('success', 'Glasses updated successfully.');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $glasses = Glasses::with('images')
        ->where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail(); // إذا ليست له -> 404

    foreach ($glasses->images as $image) {
        Storage::disk('public')->delete($image->path);
    }

    $glasses->delete();

    return redirect()
        ->route('donor.glasses.index')
        ->with('success', 'Glasses deleted successfully.');
}

public function destroyImage($glassesId, $imageId)
{
    // 1️⃣ تأكد أن النظارة تخص المستخدم الحالي
    $glasses = Glasses::where('id', $glassesId)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    // 2️⃣ تأكد أن الصورة تابعة لهذه النظارة وليست الصورة الرئيسية
    $image = $glasses->images()
        ->where('id', $imageId)
        ->where('is_primary', false) // منع حذف الصورة الرئيسية من هنا
        ->firstOrFail();

    // 3️⃣ حذف الصورة من التخزين
    if (Storage::disk('public')->exists($image->path)) {
        Storage::disk('public')->delete($image->path);
    }

    // 4️⃣ حذف السجل من قاعدة البيانات
    $image->delete();

    return back()->with('success', 'Image deleted successfully.');
}

}
