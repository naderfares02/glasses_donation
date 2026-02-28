<x-guest-layout>
    <div class="max-w-md mx-auto bg-white border rounded-2xl shadow-sm p-8 text-center">
        <h1 class="text-2xl font-bold text-gray-800">
            Phone Verification Required
        </h1>

        <p class="text-sm text-gray-600 mt-3">
            Please verify your phone number before continuing.
        </p>

        <div class="mt-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold">
                    Logout
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>