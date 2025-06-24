<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-xl font-bold mb-4">Aktifkan Notifikasi WhatsApp</h2>
            <p class="mb-4 text-gray-600 dark:text-gray-300">Dengan mengaktifkan notifikasi, Anda akan menerima
                informasi pembayaran, pengingat jadwal, dan informasi perubahan jadwal melalui WhatsApp pada nomor
                terdaftar.</p>
            <div class="border-t pt-4 mt-4">

                <form wire:submit="submit">
                    {{ $this->form }}

                    <div class="mt-6">
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center min-w-[120px]"
                            wire:loading.attr="disabled" wire:target="submit">
                            <svg wire:loading wire:target="submit" class="animate-spin h-5 w-5 mr-2 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span wire:loading.remove wire:target="submit">Simpan</span>
                            <span wire:loading wire:target="submit">Menyimpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>