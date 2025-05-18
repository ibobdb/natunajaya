<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-4 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-blue-700">Total Orders</h3>
                            <p class="text-2xl font-bold">{{ $totalOrders ?? 0 }}</p>
                            <p class="text-sm text-blue-600">All time orders</p>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-green-700">Active Course</h3>
                            <p class="text-2xl font-bold">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                            <p class="text-sm text-green-600">All time revenue</p>
                        </div>
                        
                        <div class="bg-purple-100 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-purple-700">Total Customers</h3>
                            <p class="text-2xl font-bold">{{ $totalCustomers ?? 0 }}</p>
                            <p class="text-sm text-purple-600">Registered customers</p>
                        </div>
                    </div>
                
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
