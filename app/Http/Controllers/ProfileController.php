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

    // ✅ احذف القديمة
    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        Storage::disk('public')->delete($user->avatar);
    }

    // ✅ خزّن الصورة المقصوصة (base64)
    $data = $request->input('cropped_avatar');

    // remove prefix: data:image/jpeg;base64,...
    $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
    $data = str_replace(' ', '+', $data);

    $fileName = 'avatars/' . uniqid('avatar_', true) . '.jpg';
    Storage::disk('public')->put($fileName, base64_decode($data));

    $user->update(['avatar' => $fileName]);

    return back()->with('success', 'Profile photo updated.');
}

public function destroyAvatar(Request $request)
{
    $user = $request->user();

    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        Storage::disk('public')->delete($user->avatar);
    }

    $user->update(['avatar' => null]);

    return back()->with('success', 'Profile photo removed.');
}
}
