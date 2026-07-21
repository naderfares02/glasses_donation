<?php

namespace App\Http\Controllers\donor;

use App\Http\Controllers\Controller;
use App\Models\Glasses;
use App\Models\GlassesImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Conversation;

class GlassesController extends Controller
{
    /**
     * Display a listing of the resource.
     */


public function index(Request $request)
{
    $userId = auth()->id();

    // ✅ تحديث تلقائي للحالة قبل عرض القائمة
    DB::transaction(function () use ($userId) {

        $glassesIds = Conversation::query()
            ->where('donor_id', $userId)
            ->where('status', 'open')
            ->whereNotNull('glasses_id')
            ->whereHas('messages') // ✅ رسالة واحدة أو أكثر
            ->pluck('glasses_id')
            ->unique()
            ->values();

        if ($glassesIds->isNotEmpty()) {
            Glasses::query()
                ->where('user_id', $userId)
                ->whereIn('id', $glassesIds)
                ->whereIn('status', ['available', 'reserved']) // ✅ فقط هذه تتغير
                ->update(['status' => 'in_contact']);
        }
    });

    // --------- كودك كما هو (فلترة + ترتيب) ----------
    $q = trim((string) $request->query('q', ''));
    $status = (string) $request->query('status', '');

    $query = Glasses::query()
        ->where('user_id', $userId);

    // Search
    if ($q !== '') {
        $query->where(function ($x) use ($q) {
            $x->where('title', 'like', "%{$q}%")
              ->orWhere('description', 'like', "%{$q}%");
        });
    }

    // Status filter
    if ($status !== '') {
        $query->where('status', $status);
    }

    // ترتيب الحالات (خلّيت reserved قبل available لأنه منطقي)
    $query->orderByRaw("
        FIELD(status, 'pending_donation', 'in_contact', 'reserved', 'available', 'donated')
    ");

    // ترتيب داخل الحالة
    $query->orderByDesc('created_at');

    $glasses = $query->paginate(10)->withQueryString();

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
        $data = $request->validate([
    'main_image' => ['required','image','max:4096'],
    'images.*'   => ['nullable','image','max:4096'],
    'images'     => ['nullable','array','max:3'],

    'title'       => ['required','string','max:150'],
    'description' => ['nullable','string','max:2000'],
    'condition'   => ['required','in:new,used'],

    'brand'       => ['nullable','string','max:80'],

    'lens_type'   => ['required','in:single_vision,bifocal,progressive,reading,non_prescription,other'],
    'vision_type' => ['nullable','in:distance,near,both,unknown'],

    'sph'              => ['nullable','string','max:10'],
    'cyl'              => ['nullable','string','max:10'],
    'axis'             => ['nullable','string','max:10'],
    'pd'               => ['nullable','string','max:10'],
    'prescription_note'=> ['nullable','string','max:255'],

    'frame_size'  => ['nullable','in:small,medium,large,unknown'],
    'frame_color' => ['nullable','string','max:50'],
    'age_group'   => ['nullable','in:adult,kids,teen,unknown'],
    'gender'      => ['nullable','in:male,female,unisex,unknown'],

    'pickup_city'    => ['nullable','string','max:80'],
    'contact_method' => ['nullable','in:chat_only,phone,both'],
]);

        // 2) إضافة بيانات النظام (لا تجعلها تأتي من الفورم)
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'available';

        // 3) Create + upload images — كلها لازم تنجح مع بعض أو لا شي منها
        $uploadedPaths = []; // نتابع كل مسار انكتب فعلياً عشان ننظفه لو صار خطأ لاحق

        try {
            $glasses = Glasses::create([
                'user_id' => auth()->id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'lens_type' => $data['lens_type'],
                'condition' => $data['condition'],

                'brand' => $data['brand'] ?? null,
                'vision_type' => $data['vision_type'] ?? 'unknown',

                'sph' => $data['sph'] ?? null,
                'cyl' => $data['cyl'] ?? null,
                'axis' => $data['axis'] ?? null,
                'pd' => $data['pd'] ?? null,
                'prescription_note' => $data['prescription_note'] ?? null,

                'frame_size' => $data['frame_size'] ?? 'unknown',
                'frame_color' => $data['frame_color'] ?? null,
                'age_group' => $data['age_group'] ?? 'unknown',
                'gender' => $data['gender'] ?? 'unknown',

                'pickup_city' => $data['pickup_city'] ?? null,
                'contact_method' => $data['contact_method'] ?? 'chat_only',

                'status' => 'available',
            ]);

            if ($request->hasFile('main_image')) {
                $path = $request->file('main_image')->store('glasses', 'public');
                $uploadedPaths[] = $path;

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
                    $uploadedPaths[] = $path;

                    GlassesImage::create([
                        'glasses_id' => $glasses->id,
                        'path' => $path,
                        'is_primary' => false,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to create glasses listing', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            // نظّف أي صورة انكتبت فعلياً بالتخزين قبل الفشل
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            // احذف سجل النظارة اليتيم لو كان انخلق
            if (isset($glasses)) {
                $glasses->images()->delete();
                $glasses->delete();
            }

            return back()->withInput()->with('error', 'Something went wrong while adding your glasses. Please try again.');
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
    abort_if($glasses->user_id !== auth()->id(), 403);

    $data = $request->validate([
        'title'       => ['required','string','max:150'],
        'description' => ['nullable','string','max:2000'],
        'condition'   => ['required','in:new,used'],

        'brand'       => ['nullable','string','max:80'],
        'lens_type'   => ['required','in:single_vision,bifocal,progressive,reading,non_prescription,other'],
        'vision_type' => ['nullable','in:distance,near,both,unknown'],

        'sph'               => ['nullable','string','max:10'],
        'cyl'               => ['nullable','string','max:10'],
        'axis'              => ['nullable','string','max:10'],
        'pd'                => ['nullable','string','max:10'],
        'prescription_note' => ['nullable','string','max:255'],

        'frame_size'  => ['nullable','in:small,medium,large,unknown'],
        'frame_color' => ['nullable','string','max:50'],
        'age_group'   => ['nullable','in:adult,kids,teen,unknown'],
        'gender'      => ['nullable','in:male,female,unisex,unknown'],

        'pickup_city'    => ['nullable','string','max:80'],
        'contact_method' => ['nullable','in:chat_only,phone,both'],

        // الصور
        'main_image'   => ['nullable','image','max:4096'],
        'images'       => ['nullable','array','max:3'],
        'images.*'     => ['nullable','image','max:4096'],
    ]);

    try {
        DB::transaction(function () use ($request, $glasses, $data) {

            // 1) تحديث الحقول الأساسية
            $glasses->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'condition' => $data['condition'],

                'brand' => $data['brand'] ?? null,
                'lens_type' => $data['lens_type'],
                'vision_type' => $data['vision_type'] ?? 'unknown',

                'sph' => $data['sph'] ?? null,
                'cyl' => $data['cyl'] ?? null,
                'axis' => $data['axis'] ?? null,
                'pd' => $data['pd'] ?? null,
                'prescription_note' => $data['prescription_note'] ?? null,

                'frame_size' => $data['frame_size'] ?? 'unknown',
                'frame_color' => $data['frame_color'] ?? null,
                'age_group' => $data['age_group'] ?? 'unknown',
                'gender' => $data['gender'] ?? 'unknown',

                'pickup_city' => $data['pickup_city'] ?? null,
                'contact_method' => $data['contact_method'] ?? 'chat_only',
            ]);

            // 2) استبدال الصورة الرئيسية
            if ($request->hasFile('main_image')) {

                // ارفع الصورة الجديدة الأول، قبل ما تحذف القديمة
                $path = $request->file('main_image')->store('glasses', 'public');

                // اجلب القديمة من DB (لا تعتمد على علاقة محمّلة سابقاً)
                $oldPrimary = $glasses->images()->where('is_primary', true)->first();

                // ضمان ما في أي صورة ثانية is_primary = 1
                $glasses->images()->update(['is_primary' => false]);

                $glasses->images()->create([
                    'path' => $path,
                    'is_primary' => true,
                ]);

                // الجديدة محفوظة بنجاح، هلق نأمّن نحذف القديمة
                if ($oldPrimary) {
                    Storage::disk('public')->delete($oldPrimary->path);
                    $oldPrimary->delete();
                }
            }

            // 3) استبدال الصور الإضافية بالكامل (أفضل UX في التعديل)
            if ($request->hasFile('images')) {

                // ارفع الجديدة الأول (حد أقصى 3)
                $uploads = array_slice($request->file('images'), 0, 3);
                $newPaths = [];

                foreach ($uploads as $image) {
                    $newPaths[] = $image->store('glasses', 'public');
                }

                // احذف كل الصور غير الرئيسية القديمة بعد ما تأكدنا الرفع نجح
                $oldAdditional = $glasses->images()->where('is_primary', false)->get();

                foreach ($newPaths as $path) {
                    $glasses->images()->create([
                        'path' => $path,
                        'is_primary' => false,
                    ]);
                }

                foreach ($oldAdditional as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }
        });
    } catch (\Throwable $e) {
        \Log::error('Failed to update glasses listing', [
            'glasses_id' => $glasses->id,
            'error' => $e->getMessage(),
        ]);

        return back()->withInput()->with('error', 'Something went wrong while updating your glasses. Please try again.');
    }

    // مهم: اعمل refresh عشان تشوف الصور الجديدة مباشرة
    $glasses->refresh()->load(['images', 'primaryImage']);

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
        try {
            Storage::disk('public')->delete($image->path);
        } catch (\Throwable $e) {
            // لا نوقف الحذف بسبب فشل حذف ملف واحد؛ نسجله ونكمل
            \Log::warning('Failed to delete glasses image file', [
                'path' => $image->path,
                'error' => $e->getMessage(),
            ]);
        }
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
    try {
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }
    } catch (\Throwable $e) {
        \Log::warning('Failed to delete image file from storage', [
            'path' => $image->path,
            'error' => $e->getMessage(),
        ]);
        // نكمل ونحذف السجل من الـ DB حتى لو فشل حذف الملف الفعلي،
        // لأنه الأهم إزالة المرجع من واجهة المستخدم
    }

    // 4️⃣ حذف السجل من قاعدة البيانات
    $image->delete();

    return back()->with('success', 'Image deleted successfully.');
}

}
