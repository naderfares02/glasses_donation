<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- ✅ Avatar Upload + Crop --}}
        <div class="flex flex-col items-center mb-10" x-data="{ openCrop:false }">

            {{-- ✅ Form: Update avatar --}}
            <form id="avatarForm" action="{{ route('profile.avatar.update') }}" method="POST"
                enctype="multipart/form-data" class="flex flex-col items-center">
                @csrf
                @method('PATCH')

                <label class="relative cursor-pointer group">
                    {{-- صورة المستخدم --}}
                    @if(Auth::user()->avatar)
                        <img id="avatarPreview" src="{{ asset('storage/' . Auth::user()->avatar) }}"
                            class="w-32 h-32 rounded-full object-cover border-4 border-gray-300 shadow-sm transition group-hover:opacity-80"
                            alt="avatar">
                    @else
                        {{-- Default letter avatar (نفس الحجم) --}}
                        <div id="avatarPreview"
                            class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center
                                                                                                                                   text-4xl font-bold text-gray-700 border-4 border-gray-300 shadow-sm
                                                                                                                                   transition group-hover:opacity-80">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- input مخفي فوق الصورة --}}
                    <input id="avatarInput" type="file" name="avatar"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">

                    {{-- طبقة hover --}}
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition pointer-events-none">
                        <span class="text-white text-sm font-semibold">
                            Change photo
                        </span>
                    </div>
                </label>

                {{-- ✅ Hidden base64 for cropped image (سيتم تعبئته من JS) --}}
                <input type="hidden" name="cropped_avatar" id="croppedAvatar">

                {{-- زر التحديث --}}
                {{-- <button type="submit"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Update photo
                </button> --}}
            </form>

            {{-- ✅ Form: Remove avatar (خارج الفورم الأول) --}}
            @if(auth()->user()->avatar)
                <form action="{{ route('profile.avatar.destroy') }}" method="POST" class="mt-2"
                    onsubmit="return confirm('Remove profile photo?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="px-4 py-2 bg-red-100 text-red-800 rounded-lg border hover:bg-red-200 transition">
                        Remove photo
                    </button>
                </form>
            @endif

            {{-- الأخطاء --}}
            @error('avatar')
                <span class="text-red-500 text-sm mt-3">{{ $message }}</span>
            @enderror
            @error('cropped_avatar')
                <span class="text-red-500 text-sm mt-3">{{ $message }}</span>
            @enderror

            {{-- رسالة النجاح --}}
            @if(session('success'))
                <span class="text-green-600 text-sm mt-3">{{ session('success') }}</span>
            @endif

            {{-- ✅ Crop Modal (مضمون) --}}
            <div id="cropModal" class="fixed inset-0 hidden flex items-center justify-center z-[99999]">
                {{-- Overlay --}}
                <div id="cropOverlay" class="absolute inset-0 bg-black/50"></div>

                {{-- Card --}}
                <div class="relative z-10 bg-white w-full max-w-xl rounded-2xl shadow-xl p-6 mx-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-bold text-gray-800">Crop your photo</p>
                            <p class="text-sm text-gray-600 mt-1">Drag to adjust, then click Save.</p>
                        </div>
                        <button type="button" id="closeCrop" class="p-2 rounded-lg hover:bg-gray-100">✕</button>
                    </div>

                    <div class="mt-5">
                        <div class="w-full h-[420px] bg-gray-50 border rounded-xl overflow-hidden relative">
                            <img id="cropImage" style="display:block; max-width:100%;" alt="Crop image">
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" id="cancelCrop"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                            Cancel
                        </button>

                        <button type="button" id="saveCrop"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                            Save
                        </button>
                    </div>
                </div>
            </div>




        </div>


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                console.log("✅ Crop script loaded");

                // ✅ تأكيد أن Cropper محمّل
                if (typeof Cropper === 'undefined') {
                    console.error("❌ Cropper is NOT loaded. Add cropper.min.js in the layout before scripts stack.");
                    return;
                }

                let cropper = null;

                const input = document.getElementById('avatarInput');
                const form = document.getElementById('avatarForm');

                const modal = document.getElementById('cropModal');
                const overlay = document.getElementById('cropOverlay');
                const cropImage = document.getElementById('cropImage');

                const closeBtn = document.getElementById('closeCrop');
                const cancelBtn = document.getElementById('cancelCrop');
                const saveBtn = document.getElementById('saveCrop');

                const hidden = document.getElementById('croppedAvatar');

                if (!input || !form || !modal || !overlay || !cropImage || !saveBtn || !hidden) {
                    console.error("❌ Missing elements. Check IDs in HTML.");
                    return;
                }


                function openModal() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    console.log("✅ Modal opened");
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');

                    if (cropper) { cropper.destroy(); cropper = null; }
                    cropImage.src = '';
                    console.log("✅ Modal closed");
                }


                overlay.addEventListener('click', closeModal);
                closeBtn.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', closeModal);

                input.addEventListener('change', function (e) {
                    const file = e.target.files?.[0];
                    if (!file) return;

                    console.log("✅ File selected:", file.name);

                    // ✅ افتح المودال قبل init
                    openModal();

                    cropImage.src = URL.createObjectURL(file);

                    cropImage.onload = function () {
                        setTimeout(() => {
                            if (cropper) cropper.destroy();

                            cropper = new Cropper(cropImage, {
                                aspectRatio: 1,
                                viewMode: 1,
                                autoCropArea: 1,
                                dragMode: 'move',
                                background: false,
                                responsive: true,
                                movable: true,
                                zoomable: true,
                                scalable: false,
                                rotatable: false,
                            });

                            console.log("✅ Cropper initialized");
                        }, 150);
                    };

                    // ✅ مهم: امنع الفورم من الإرسال إذا ما تم القص بعد اختيار صورة
                    form.addEventListener('submit', function (e) {
                        // إذا المستخدم اختار ملف ولسه ما عمل save crop
                        if (input.files?.length && !hidden.value) {
                            e.preventDefault();
                            openModal();
                        }
                    });

                    saveBtn.addEventListener('click', function () {
                        console.log("✅ Save clicked");

                        if (!cropper) {
                            console.warn("⚠️ Cropper not initialized");
                            return;
                        }

                        const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });

                        hidden.value = canvas.toDataURL('image/jpeg', 0.9);

                        console.log("✅ Cropped data set. Submitting form...");
                        closeModal();
                        form.submit();
                    });

                    const zoomInBtn = document.getElementById('zoomIn');
                    const zoomOutBtn = document.getElementById('zoomOut');
                    const resetBtn = document.getElementById('resetCrop');

                    zoomInBtn?.addEventListener('click', () => cropper?.zoom(0.1));
                    zoomOutBtn?.addEventListener('click', () => cropper?.zoom(-0.1));
                    resetBtn?.addEventListener('click', () => cropper?.reset());

                });
            });

        </script>
    @endpush



</x-app-layout>