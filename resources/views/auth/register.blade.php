<x-guest-layout>
    <div class="w-full">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Create account</h1>
            <p class="text-sm text-gray-600 mt-1">Register as donor or recipient to start.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Role --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Register as</label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="donor" class="peer hidden"
                            {{ old('role') === 'donor' ? 'checked' : '' }} required>
                        <div
                            class="p-4 rounded-xl border bg-white hover:bg-gray-50 transition
                                   peer-checked:border-blue-600 peer-checked:bg-blue-50">
                            <p class="font-bold text-gray-800">Donor</p>
                            <p class="text-sm text-gray-600 mt-1">I want to donate glasses.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="recipient" class="peer hidden"
                            {{ old('role') === 'recipient' ? 'checked' : '' }} required>
                        <div
                            class="p-4 rounded-xl border bg-white hover:bg-gray-50 transition
                                   peer-checked:border-blue-600 peer-checked:bg-blue-50">
                            <p class="font-bold text-gray-800">Recipient</p>
                            <p class="text-sm text-gray-600 mt-1">I want to request glasses.</p>
                        </div>
                    </label>
                </div>

                @error('role')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700">Name</label>
                <input name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                @error('name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                    class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-blue-200 focus:border-blue-600"
                           placeholder="you@example.com">
                @error('email') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Phone + City --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Phone</label>
                    <input name="phone" type="text" value="{{ old('phone') }}" required
                        placeholder="+4915123456789" pattern="^\+49[0-9]{9,12}$"
                        class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                    <p class="text-xs text-gray-500 mt-1">Format: +49XXXXXXXXX</p>
                    @error('phone') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">City</label>
                    <select name="city" required
                        class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                        <option value="" disabled {{ old('city') ? '' : 'selected' }}>Select city</option>
                        @php $cities=["Berlin","Hamburg","Munich","Cologne","Frankfurt","Stuttgart","Düsseldorf","Dortmund","Essen","Leipzig","Bremen","Dresden","Hanover","Nuremberg","Duisburg","Bochum","Wuppertal","Bielefeld","Bonn","Münster"]; @endphp
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ old('city')===$city?'selected':'' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                    @error('city') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <input name="password" type="password" required autocomplete="new-password"
                    class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                @error('password') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Confirm --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                <input name="password_confirmation" type="password" required autocomplete="new-password"
                    class="mt-1 w-full border rounded-xl px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
                @error('password_confirmation') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Terms --}}
<div class="mt-4">
    <label class="flex items-start gap-3">
        <input
            type="checkbox"
            name="terms"
            value="1"
            {{ old('terms') ? 'checked' : '' }}
            required
            class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
        >

        <span class="text-sm text-gray-700 leading-relaxed">
            I agree to the
            <a href="{{ route('terms') }}" target="_blank" class="font-semibold text-blue-700 hover:text-blue-800 underline">
                Terms of Use
            </a>
            and
            <a href="{{ route('privacy') }}" target="_blank" class="font-semibold text-blue-700 hover:text-blue-800 underline">
                Privacy Policy
            </a>.
        </span>
    </label>

    @error('terms')
        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>

            <div class="flex items-center justify-between mt-4">
                <a class="font-semibold text-blue-700 hover:text-blue-800"
                   href="{{ route('login') }}">
                    Already registered?
                </a>

                <button type="submit"
                    class="ms-3 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    Register
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>