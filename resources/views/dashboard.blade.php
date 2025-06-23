<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-4 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-blue-700">Total Pesanan</h3>
                            <p class="text-2xl font-bold">{{ $totalOrders ?? 0 }}</p>
                            <p class="text-sm text-blue-600">Semua pesanan</p>
                        </div>

                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-green-700">Kursus Aktif</h3>
                            <p class="text-2xl font-bold">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                            <p class="text-sm text-green-600">Total pendapatan</p>
                        </div>

                        <div class="bg-purple-100 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-purple-700">Total Pelanggan</h3>
                            <p class="text-2xl font-bold">{{ $totalCustomers ?? 0 }}</p>
                            <p class="text-sm text-purple-600">Pelanggan terdaftar</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>