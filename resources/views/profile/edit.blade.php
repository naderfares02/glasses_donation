{{-- resources/views/profile/edit.blade.php --}}
<x-app-layout>
    @push('styles')
        {{-- Cropper CSS --}}
        <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
        <style>
            .cropper-view-box,
            .cropper-face {
                border-radius: 9999px;
            }
        </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
                <p class="text-sm text-gray-500 mt-1">Update your photo, personal info and password.</p>
            </div>
            <a href="{{ url()->previous() }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Top: Avatar + Summary --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Avatar card --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
{{-- Avatar card --}}
<div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b bg-gray-50">
        <p class="text-sm font-semibold text-gray-800">Profile Photo</p>
        <p class="text-xs text-gray-500 mt-1">Upload, crop, and save your avatar.</p>
    </div>

    <div class="p-6 space-y-4">
        {{-- ✅ Update avatar form --}}
        <form id="avatarForm" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="flex flex-col items-center text-center">
                <label class="relative group cursor-pointer">
                    @if(auth()->user()->avatar)
                        <img id="avatarPreview" src="{{ asset('storage/' . auth()->user()->avatar) }}"
                             class="w-28 h-28 rounded-full object-cover border shadow-sm"
                             alt="avatar">
                    @else
                        <div id="avatarPreview"
                             class="w-28 h-28 rounded-full bg-gray-100 border shadow-sm flex items-center justify-center text-3xl font-extrabold text-gray-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 rounded-full bg-black/45 opacity-0 group-hover:opacity-100 transition
                                flex items-center justify-center pointer-events-none">
                        <span class="text-white text-xs font-semibold">Change</span>
                    </div>

                    <input id="avatarInput" type="file" name="avatar" accept="image/*" class="hidden">
                </label>

                <p class="my-3 text-xs text-gray-500">
                    Recommended: square image. We will crop it to a circle.
                </p>
            </div>

            {{-- hidden base64 --}}
            <input type="hidden" name="cropped_avatar" id="croppedAvatar">

            <div class="flex items-center gap-1">
                <button type="button" id="openFileBtn"
                        class="flex-1 px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                    Upload photo
                </button>
            </div>

            @error('avatar')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
            @error('cropped_avatar')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </form>

        @if(auth()->user()->avatar)
            <form action="{{ route('profile.avatar.destroy') }}" method="POST"
                  onsubmit="return confirm('Remove profile photo?');">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl border bg-red-50 hover:bg-red-100 text-sm font-semibold text-red-700" style="margin-top: -5px">
                    Remove photo
                </button>
            </form>
        @endif
    </div>
</div>
                </div>

                {{-- Summary card --}}
                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Account Summary</p>
                        <p class="text-xs text-gray-500 mt-1">Your basic details.</p>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border
                                {{ auth()->user()->role === 'donor' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                {{ auth()->user()->role === 'recipient' ? 'bg-purple-50 text-purple-700 border-purple-200' : '' }}
                                {{ in_array(auth()->user()->role, ['admin', 'super_admin']) ? 'bg-gray-100 text-gray-800 border-gray-200' : '' }}
                            ">
                                {{ strtoupper(auth()->user()->role) }}
                            </span>

                            @if(auth()->user()->phone_verified_at)
                                <span
                                    class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full border bg-green-50 text-green-700 border-green-200">
                                    ✅ Phone verified
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                                    ⚠️ Phone not verified
                                </span>
                            @endif

                            @if(auth()->user()->email_verified_at)
                                <span
                                    class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full border bg-green-50 text-green-700 border-green-200">
                                    ✅ email verified
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                                    ⚠️ email not verified
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="border rounded-2xl p-4 bg-white">
                                <p class="text-xs text-gray-500">Name</p>
                                <p class="font-semibold text-gray-800 mt-1 truncate">{{ auth()->user()->name }}</p>
                            </div>

                            <div class="border rounded-2xl p-4 bg-white">
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="font-semibold text-gray-800 mt-1 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="border rounded-2xl p-4 bg-white">
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="font-semibold text-gray-800 mt-1 truncate">{{ auth()->user()->phone ?: '—' }}
                                </p>
                            </div>

                            <div class="border rounded-2xl p-4 bg-white">
                                <p class="text-xs text-gray-500">City</p>
                                <p class="font-semibold text-gray-800 mt-1 truncate">{{ auth()->user()->city ?: '—' }}
                                </p>
                            </div>

                            <div class="border rounded-2xl p-4 bg-white">
                                <p class="text-xs text-gray-500">Joined</p>
                                <p class="font-semibold text-gray-800 mt-1">
                                    {{ auth()->user()->created_at?->format('Y-m-d') ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Forms grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Personal Information</p>
                        <p class="text-xs text-gray-500 mt-1">Update your name, phone and city.</p>
                    </div>
                    <div class="p-6">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b bg-gray-50">
                        <p class="text-sm font-semibold text-gray-800">Security</p>
                        <p class="text-xs text-gray-500 mt-1">Change your password.</p>
                    </div>
                    <div class="p-6">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

            </div>

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b bg-gray-50">
                    <p class="text-sm font-semibold text-gray-800">Danger Zone</p>
                    <p class="text-xs text-gray-500 mt-1">Delete your account permanently.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>

    {{-- Crop Modal --}}
    <div id="cropModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4">
        <div id="cropOverlay" class="absolute inset-0 bg-black/50"></div>

        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b bg-gray-50 flex items-start justify-between">
                <div>
                    <p class="text-lg font-bold text-gray-800">Crop your photo</p>
                    <p class="text-sm text-gray-600 mt-1">Adjust then click Save.</p>
                </div>
                <button type="button" id="closeCrop" class="px-3 py-2 rounded-xl hover:bg-gray-100 text-gray-700">
                    ✕
                </button>
            </div>

            <div class="p-5">
                <div
                    class="w-full h-[420px] bg-gray-50 border rounded-xl overflow-hidden flex items-center justify-center">
                    <img id="cropImage" alt="Crop image" class="max-w-full block">
                </div>

                <div class="mt-5 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <div class="flex gap-2">
                        <button type="button" id="zoomOut"
                            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                            −
                        </button>
                        <button type="button" id="zoomIn"
                            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                            +
                        </button>
                        <button type="button" id="resetCrop"
                            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                            Reset
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="cancelCrop"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold">
                            Cancel
                        </button>
                        <button type="button" id="saveCrop"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Cropper JS --}}
        <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Cropper === 'undefined') {
        console.error("Cropper is not loaded");
        return;
    }

    const form = document.getElementById('avatarForm');
    const input = document.getElementById('avatarInput');
    const openBtn = document.getElementById('openFileBtn');
    const hidden = document.getElementById('croppedAvatar');

    const modal = document.getElementById('cropModal');
    const overlay = document.getElementById('cropOverlay');
    const cropImage = document.getElementById('cropImage');
    const closeBtn = document.getElementById('closeCrop');
    const cancelBtn = document.getElementById('cancelCrop');
    const saveBtn = document.getElementById('saveCrop');

    let cropper = null;

    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (cropper) { cropper.destroy(); cropper = null; }
        cropImage.src = '';
    };

    openBtn?.addEventListener('click', () => input.click());
    overlay?.addEventListener('click', closeModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    input.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // ✅ كل مرة نختار ملف جديد، نفرّغ الـ hidden
        hidden.value = '';

        openModal();
        cropImage.src = URL.createObjectURL(file);

        cropImage.onload = () => {
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                dragMode: 'move',
                background: false,
                responsive: true,
            });
        };
    });

    saveBtn.addEventListener('click', () => {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({ width: 500, height: 500 });
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        hidden.value = dataUrl;

        const preview = document.getElementById('avatarPreview');
        if (preview?.tagName === 'IMG') {
            preview.src = dataUrl;
        } else if (preview) {
            const img = document.createElement('img');
            img.id = 'avatarPreview';
            img.src = dataUrl;
            img.className = "w-28 h-28 rounded-full object-cover border shadow-sm";
            img.alt = "avatar";
            preview.replaceWith(img);
        }

        closeModal();

        form.submit();
    });

    form.addEventListener('submit', (e) => {
        if (input.files?.length && !hidden.value) {
            e.preventDefault();
            openModal();
        }
    });
});
</script>
    @endpush
</x-app-layout>