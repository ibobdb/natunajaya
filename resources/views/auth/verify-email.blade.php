<x-guest-layout>
    <div class="flex flex-col items-center">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Natuna Driving Academy" class="h-16 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Verifikasi Email</h2>
        <p class="text-gray-600 mb-6">Kami telah mengirimkan email verifikasi ke akun Anda</p>
    </div>

    <div class="mb-6 text-sm text-gray-600 bg-blue-50 p-4 rounded-md border border-blue-100">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
    <div class="mb-6 text-sm bg-green-50 border border-green-200 text-green-600 p-4 rounded-md">
        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
    </div>
    @endif

    <div class="mt-6 space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                {{ __('Kirim Ulang Email Verifikasi') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</x-guest-layout>