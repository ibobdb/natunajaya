<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Pembayaran untuk Faktur: {{ $this->invoiceNumber }}
        </x-slot>

        @if($this->order)
        <div class="space-y-6">
            <x-filament::card class="filament-payment-summary dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-8 mb-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 py-4">Ringkasan Pembayaran</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Kursus:</span>
                            <span
                                class="text-gray-900 dark:text-gray-100">{{ $this->order->course->name ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Deskripsi:</span>
                            <span
                                class="text-gray-500 dark:text-gray-100 text-sm">{{ $this->order->course->description ?? 'Tidak ada deskripsi' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Durasi:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $this->order->course->duration ?? 'N/A' }}
                                {{ ($this->order->course->duration_session ?? 'month') == 'month' ? 'bulan' : (($this->order->course->duration_session ?? 'month') == 'week' ? 'minggu' : 'tahun') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Sesi:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $this->order->course->session ?? 'N/A' }}
                                X</span>
                        </div>
                    </div>
                    <div class="space-y-3 ">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Jumlah:</span>
                            <span
                                class="font-medium text-gray-900 dark:text-white">{{ number_format($this->order->amount, 0, ',', '.') }}
                                IDR</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">Status:</span>
                            <x-filament::badge
                                :color="$this->order->status == 'success' ? 'success' : ($this->order->status == 'pending' ? 'warning' : 'danger')">
                                {{ $this->order->status == 'success' ? 'Berhasil' : ($this->order->status == 'pending' ? 'Tertunda' : ($this->order->status == 'failed' ? 'Gagal' : ucfirst($this->order->status))) }}
                            </x-filament::badge>
                        </div>

                        @if($this->order->payment_date)
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Tanggal Pembayaran:</span>
                            <span
                                class="text-gray-900 dark:text-gray-100">{{ $this->order->payment_date->format('d M Y') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Nomor Faktur:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ $this->invoiceNumber }}</span>
                        </div>


                    </div>
                </div>
            </x-filament::card>

            @if($this->order->status !== 'success' && $this->order->status !== 'paid')
            <div class="mt-4 flex justify-end">
                @if(!$this->snapToken)
                <x-filament::button type="button" color="primary" wire:loading.attr="disabled"
                    wire:target="processPayment" wire:loading.class="opacity-50 cursor-not-allowed"
                    wire:click="processPayment">
                    Siapkan Pembayaran
                </x-filament::button>
                @else
                <x-filament::button type="button" color="success" wire:loading.attr="disabled"
                    wire:target="initiateMidtransPayment" wire:loading.class="opacity-50 cursor-not-allowed"
                    wire:click="initiateMidtransPayment">
                    Bayar Sekarang
                </x-filament::button>
                @endif
            </div>
            @endif
        </div>
        @else
        <x-filament::card>
            <div class="flex items-center justify-center py-4">
                <x-filament::icon icon="heroicon-o-exclamation-circle"
                    class="h-5 w-5 text-gray-400 dark:text-gray-500 mr-2" />
                <p class="text-gray-500 dark:text-gray-400">Tidak ada informasi pembayaran tersedia untuk faktur ini.
                </p>
            </div>
        </x-filament::card>
        @endif
    </x-filament::section>
</x-filament-panels::page>

<!-- Midtrans Snap JS -->
<script
    src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    function setupMidtransEventHandler() {
        Livewire.on('openMidtransPopup', event => {
            console.log('Pop-up pembayaran dipicu dengan token:', event.snapToken ? 'Token ada' : 'Tidak ada token');                // Buka pop-up Midtrans Snap dengan opsi konfigurasi
            window.snap.pay(event.snapToken, {
                skipOrderSummary: true,
                showOrderId: true,        
                uiMode: "modern",
                onSuccess: function(result){        
                    // Jalankan handle midtrans callback controller melalui Livewire
                @this.paymentSuccess(result);
                },
                onPending: function(result){
                    console.log('Pembayaran tertunda', result);
                    Livewire.dispatch('paymentPending', { result: result });
                },
                onError: function(result){
                    console.log('Error pembayaran', result);
                    Livewire.dispatch('paymentError', { result: result });
                },
                onClose: function(){
                    console.log('Pop-up pembayaran ditutup');
                    Livewire.dispatch('paymentCancelled');
                }
            });
        });
    }

    // Pasang handler pada awalnya
    document.addEventListener('livewire:initialized', function() {
        setupMidtransEventHandler();
    });

    // Inisialisasi ulang handler ketika Livewire melakukan update
    document.addEventListener('livewire:navigated', function() {
        setupMidtransEventHandler();
    });
    
    // Juga menangani update yang tidak melibatkan navigasi
    document.addEventListener('livewire:update', function() {
        setupMidtransEventHandler();
    });
</script>