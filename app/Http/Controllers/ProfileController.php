<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateAvatar(Request $request)
{
    // ✅ نسمح بوجود avatar لأننا نختاره، لكن الحفظ سيكون من cropped_avatar
    if (!$request->filled('cropped_avatar')) {
        return back()->with('error', 'No cropped image received.');
    }

    $request->validate([
        'avatar' => ['required', 'image', 'max:2048'], // لازم يختار ملف (لأن القص يبدأ منه)
        'cropped_avatar' => ['required', 'string'],
    ]);

    $user = $request->user();

    // ✅ خزّن الصورة المقصوصة (base64) أولاً، قبل حذف القديمة
    // (لو فشل الحفظ، ما نكون خسرنا الصورة القديمة بلا داعي)
    $data = $request->input('cropped_avatar');

    // remove prefix: data:image/jpeg;base64,...
    $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
    $data = str_replace(' ', '+', $data);

    $decoded = base64_decode($data, strict: true);

    if ($decoded === false || strlen($decoded) < 100) {
        // strict mode بيرجع false لو فيه أحرف غير صالحة بالـ base64
        // وفحص الحجم بيلقط الحالات اللي بترجع string فاضي أو تالف
        \Log::warning('Avatar upload failed: invalid base64 data', ['user_id' => $user->id]);
        return back()->with('error', 'Failed to process the image. Please try a different photo.');
    }

    try {
        $fileName = 'avatars/' . uniqid('avatar_', true) . '.jpg';
        Storage::disk('public')->put($fileName, $decoded);

        // ✅ تأكد إنه الملف فعلاً انكتب قبل ما نحذف القديم أو نحدّث الـ DB
        if (!Storage::disk('public')->exists($fileName)) {
            throw new \RuntimeException('File write verification failed.');
        }
    } catch (\Throwable $e) {
        \Log::error('Avatar upload failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
        ]);
        return back()->with('error', 'Something went wrong while saving your photo. Please try again.');
    }

    // ✅ الحفظ الجديد نجح فعلياً، هلق نأمّن نحذف القديمة
    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        try {
            Storage::disk('public')->delete($user->avatar);
        } catch (\Throwable $e) {
            // مو حرج: الصورة الجديدة موجودة أصلاً، بس نسجل الفشل بحذف القديمة
            \Log::warning('Failed to delete old avatar', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    $user->update(['avatar' => $fileName]);

    return back()->with('success', 'Profile photo updated.');
}

public function destroyAvatar(Request $request)
{
    $user = $request->user();

    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        try {
            Storage::disk('public')->delete($user->avatar);
        } catch (\Throwable $e) {
            \Log::warning('Failed to delete avatar file', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            // نكمل حتى لو فشل حذف الملف الفعلي، لأن الأهم إزالة المرجع من الـ DB
        }
    }

    $user->update(['avatar' => null]);

    return back()->with('success', 'Profile photo removed.');
}
}
