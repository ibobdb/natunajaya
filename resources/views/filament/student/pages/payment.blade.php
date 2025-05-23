<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Payment for Invoice: {{ $this->invoiceNumber }}
        </x-slot>
        
        @if($this->order)
            <div class="space-y-6">
                <x-filament::card class="filament-payment-summary dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-8 mb-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 py-4">Payment Summary</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                      <div class="space-y-3">
                        <div class="flex justify-between">
                          <span class="text-gray-500 dark:text-gray-400">Course:</span>
                          <span class="text-gray-900 dark:text-gray-100">{{ $this->order->course->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                          <span class="text-gray-500 dark:text-gray-400">Description:</span>
                          <span class="text-gray-900 dark:text-gray-100">{{ $this->order->course->description ?? 'No description available' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                          <span class="text-gray-500 dark:text-gray-400">Duration:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $this->order->course->duration ?? 'N/A' }} {{ $this->order->course->duration_session ?? 'month' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-500 dark:text-gray-400">Session:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $this->order->course->session ?? 'N/A' }} X</span>
                        </div>
                      </div>
                        <div class="space-y-3 ">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Amount:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($this->order->amount, 0, ',', '.') }} IDR</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                <x-filament::badge
                                    :color="$this->order->status == 'success' ? 'success' : ($this->order->status == 'pending' ? 'warning' : 'danger')"
                                >
                                    {{ ucfirst($this->order->status) }}
                                </x-filament::badge>
                            </div>
                            
                            @if($this->order->payment_date)
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Payment Date:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $this->order->payment_date->format('d M Y') }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Invoice Number:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $this->invoiceNumber }}</span>
                            </div>
                            
                        
                        </div>
                    </div>
                </x-filament::card>
                
                @if($this->order->status !== 'success' && $this->order->status !== 'paid')
                <div class="mt-4 flex justify-end">                
                  <x-filament::button
                    type="button"
                    color="primary"
                    wire:loading.attr="disabled"
                    wire:target="processPayment"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    wire:click="{{ $this->snapToken ? 'initiateMidtransPayment' : 'processPayment' }}"
                  >
                    {{ $this->snapToken ? 'Pay Now' : 'Prepare Payment' }}
                  </x-filament::button>
                </div>
                @endif
            </div>
        @else
            <x-filament::card>
                <div class="flex items-center justify-center py-4">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-circle"
                        class="h-5 w-5 text-gray-400 dark:text-gray-500 mr-2"
                    />
                    <p class="text-gray-500 dark:text-gray-400">No payment information available for this invoice.</p>
                </div>
            </x-filament::card>
        @endif
    </x-filament::section>
</x-filament-panels::page>

<!-- Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    // Listen for the custom event from Livewire
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('openMidtransPopup', event => {
            // Open Midtrans Snap popup with configuration options
            window.snap.pay(event.snapToken, {
                // Override the default behavior to keep the background visible
                skipOrderSummary: true,
                showOrderId: true,
                gopayMode: "deeplink",
                uiMode: "modern",
                onSuccess: function(result){
                    // Success handler - you can refresh page or notify Livewire to update
                    Livewire.dispatch('paymentSuccess', { result: result });
                },
                onPending: function(result){
                    // Pending handler
                    Livewire.dispatch('paymentPending', { result: result });
                },
                onError: function(result){
                    // Error handler
                    Livewire.dispatch('paymentError', { result: result });
                },
                onClose: function(){
                    // Handle when customer closes the popup without completing payment
                    Livewire.dispatch('paymentCancelled');
                }
            });
        });
    });
</script>

