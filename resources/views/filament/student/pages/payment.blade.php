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
                                    :color="$this->order->status == 'paid' ? 'success' : ($this->order->status == 'pending' ? 'warning' : 'danger')"
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
                
                @if($this->order->status !== 'paid')
                <div class="mt-4 flex justify-end">
                    <x-filament::button
                        type="button"
                        color="primary"
                        wire:click="processPayment"
                    >
                        Proceed to Payment
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
