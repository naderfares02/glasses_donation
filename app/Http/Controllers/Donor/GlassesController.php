<?php

namespace App\Http\Controllers\Donor;

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
    // ملاحظة: استخدمنا CASE WHEN بدل FIELD() لأن FIELD() خاصة بـ MySQL فقط
    // ولا تعمل على SQLite (المستخدمة في بيئة الاختبارات)
    $query->orderByRaw("
        CASE status
            WHEN 'pending_donation' THEN 1
            WHEN 'in_contact' THEN 2
            WHEN 'reserved' THEN 3
            WHEN 'available' THEN 4
            WHEN 'donated' THEN 5
            ELSE 6
        END
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
    // الصورة الرئيسية اختيارية: ممكن يضاف لاحقاً من صفحة التعديل
    'main_image' => ['nullable','image','max:4096'],
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
        $data['user_id'] = auth()->id();
        $data['status'] = 'available';

        // 3) Create
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

$glasses->serial_number = 'GL-' . date('Y') . '-' . str_pad(
    $glasses->id,
    6,
    '0',
    STR_PAD_LEFT
);

$glasses->save();

        if ($request->hasFile('main_image')) {
            try {
                $path = $request->file('main_image')->store('glasses', 'public');

                GlassesImage::create([
                    'glasses_id' => $glasses->id,
                    'path' => $path,
                    'is_primary' => true,
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to upload main glasses image', [
                    'glasses_id' => $glasses->id,
                    'error' => $e->getMessage(),
                ]);

                $glasses->delete(); // ارجع تحذف السجل الناقص بدل ما يضل يتيم بدون صورة
                return back()->withInput()->with('error', 'Image upload failed. Please try again.');
            }
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
    $this->authorize('view', $glasses);

    $glasses->load(['primaryImage', 'images']);

    return view('donor.glasses_show', compact('glasses'));
}


    /**
     * Show the form for editing the specified resource.
     */
   public function edit(Glasses $glasses)
{
    $this->authorize('update', $glasses);

    $glasses->load(['images', 'primaryImage']);

    return view('donor.edit_glass', compact('glasses'));
}

    /**
     * Update the specified resource in storage.
     */


public function update(Request $request, Glasses $glasses)
{
    $this->authorize('update', $glasses);

    // ملاحظة: title/condition/lens_type بتستخدم "sometimes" عشان تحديث جزئي
    // (partial update) من صفحة التعديل ما يفشلش لو الحقل ما اتبعتش أصلاً؛
    // ولو اتبعت فعلاً لازم يكون صحيح ("required" لسه سارية وقتها).
    $data = $request->validate([
        'title'       => ['sometimes','required','string','max:150'],
        'description' => ['nullable','string','max:2000'],
        'condition'   => ['sometimes','required','in:new,used'],

        'brand'       => ['nullable','string','max:80'],
        'lens_type'   => ['sometimes','required','in:single_vision,bifocal,progressive,reading,non_prescription,other'],
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
            // (?? $glasses->xxx بدل ما نطلب الحقل إجباري كل مرة في تحديث جزئي)
            $glasses->update([

                'title' => $data['title'] ?? $glasses->title,
                'description' => array_key_exists('description', $data) ? $data['description'] : $glasses->description,
                'condition' => $data['condition'] ?? $glasses->condition,

                'brand' => array_key_exists('brand', $data) ? $data['brand'] : $glasses->brand,
                'lens_type' => $data['lens_type'] ?? $glasses->lens_type,
                'vision_type' => array_key_exists('vision_type', $data) ? $data['vision_type'] : $glasses->vision_type,

                'sph' => array_key_exists('sph', $data) ? $data['sph'] : $glasses->sph,
                'cyl' => array_key_exists('cyl', $data) ? $data['cyl'] : $glasses->cyl,
                'axis' => array_key_exists('axis', $data) ? $data['axis'] : $glasses->axis,
                'pd' => array_key_exists('pd', $data) ? $data['pd'] : $glasses->pd,
                'prescription_note' => array_key_exists('prescription_note', $data) ? $data['prescription_note'] : $glasses->prescription_note,

                'frame_size' => array_key_exists('frame_size', $data) ? $data['frame_size'] : $glasses->frame_size,
                'frame_color' => array_key_exists('frame_color', $data) ? $data['frame_color'] : $glasses->frame_color,
                'age_group' => array_key_exists('age_group', $data) ? $data['age_group'] : $glasses->age_group,
                'gender' => array_key_exists('gender', $data) ? $data['gender'] : $glasses->gender,

                'pickup_city' => array_key_exists('pickup_city', $data) ? $data['pickup_city'] : $glasses->pickup_city,
                'contact_method' => array_key_exists('contact_method', $data) ? $data['contact_method'] : $glasses->contact_method,
            ]);

            // 2) استبدال الصورة الرئيسية
            if ($request->hasFile('main_image')) {

                // اجلب القديمة من DB (لا تعتمد على علاقة محمّلة سابقاً)
                $oldPrimary = $glasses->images()->where('is_primary', true)->first();

                if ($oldPrimary) {
                    Storage::disk('public')->delete($oldPrimary->path);
                    $oldPrimary->delete();
                }

                // ضمان ما في أي صورة ثانية is_primary = 1
                $glasses->images()->update(['is_primary' => false]);

                $path = $request->file('main_image')->store('glasses', 'public');

                if (!$path) {
                    throw new \RuntimeException('Failed to store main glasses image.');
                }

                $glasses->images()->create([
                    'path' => $path,
                    'is_primary' => true,
                ]);
            }

            // 3) استبدال الصور الإضافية بالكامل (أفضل UX في التعديل)
            if ($request->hasFile('images')) {

                // احذف كل الصور غير الرئيسية القديمة
                $oldAdditional = $glasses->images()->where('is_primary', false)->get();

                foreach ($oldAdditional as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }

                // خزّن الجديدة (حد أقصى 3)
                $uploads = array_slice($request->file('images'), 0, 3);

                foreach ($uploads as $image) {
                    $path = $image->store('glasses', 'public');

                    if (!$path) {
                        throw new \RuntimeException('Failed to store additional glasses image.');
                    }

                    $glasses->images()->create([
                        'path' => $path,
                        'is_primary' => false,
                    ]);
                }
            }
        });
    } catch (\Throwable $e) {
        \Log::error('Failed to update glasses images', [
            'glasses_id' => $glasses->id,
            'error' => $e->getMessage(),
        ]);

        return back()->withInput()->with('error', 'Image upload failed. Please try again.');
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
    public function destroy(Glasses $glasses)
{
    $this->authorize('delete', $glasses);

    $glasses->load('images');

    foreach ($glasses->images as $image) {
        Storage::disk('public')->delete($image->path);
    }

    $glasses->delete();

    return redirect()
        ->route('donor.glasses.index')
        ->with('success', 'Glasses deleted successfully.');
}

public function destroyImage(Glasses $glasses, GlassesImage $image)
{
    // 1️⃣ تأكد أن النظارة تخص المستخدم الحالي
    $this->authorize('update', $glasses);

    // 2️⃣ تأكد أن الصورة تابعة لهذه النظارة وليست الصورة الرئيسية
    abort_if($image->glasses_id !== $glasses->id || $image->is_primary, 404);

    // 3️⃣ حذف الصورة من التخزين
    if (Storage::disk('public')->exists($image->path)) {
        Storage::disk('public')->delete($image->path);
    }

    // 4️⃣ حذف السجل من قاعدة البيانات
    $image->delete();

    return back()->with('success', 'Image deleted successfully.');
}

}