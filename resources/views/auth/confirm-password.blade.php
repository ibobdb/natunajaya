<x-guest-layout>
    <div class="flex flex-col items-center">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Natuna Driving Academy" class="h-16 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Konfirmasi Password</h2>
        <p class="text-gray-600 mb-6">Ini adalah area yang aman. Konfirmasi password Anda untuk melanjutkan.</p>
    </div>

    <div class="mb-6 text-sm text-gray-600 bg-blue-50 p-4 rounded-md border border-blue-100">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <x-text-input id="password"
                    class="block mt-1 w-full pl-10 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    type="password" name="password" required autocomplete="current-password"
                    placeholder="Masukkan password Anda" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                {{ __('Konfirmasi') }}
            </button>
        </div>
    </form>
</x-guest-layout>