<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-4">Order Details</h2>
                    <div class="mb-6">
                        <p><strong>Invoice ID:</strong> {{ $order->invoice_id }}</p>
                        <p><strong>Course:</strong> {{ $order->course->name ?? 'N/A' }}</p>
                        <p><strong>Amount:</strong> {{ number_format($order->amount) }}</p>
                        <p><strong>Final Amount:</strong> {{ number_format($order->final_amount) }}</p>
                    </div>

                    <h3 class="text-xl font-bold mb-4">Payment Method</h3>
                    <form action="{{ route('student.payment.process', ['order' => $order->id]) }}" method="POST">
                        @csrf
                        <!-- Add your payment form fields here -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="payment_method">
                                Payment Method
                            </label>
                            <select 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                id="payment_method" 
                                name="payment_method"
                            >
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <button 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                                type="submit"
                            >
                                Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
