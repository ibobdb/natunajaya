<div
    class="bg-white rounded-lg z-99 shadow-2xl p-8 w-full max-w-md transform hover:scale-[1.02] transition-transform duration-300">
    <h3 class="text-gray-900 text-xl font-semibold mb-2 flex items-center">
        <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
        </svg>
        Cek Jadwal Pelatihan
    </h3>
    <p class="text-gray-600 text-sm mb-4">
        Ingin tahu jadwal tersedia? Cek ketersediaan jadwal instruktur dan kelas kami sekarang.
    </p>

    <form id="checkScheduleForm" action="{{ route('check-schedule') }}" method="GET" class="space-y-3">
        <div class="flex space-x-2">
            <div class="w-1/2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <input type="date" name="date" class="w-full pl-9 pr-3 py-2 border rounded text-sm"
                    min="{{ date('Y-m-d') }}" />
            </div>
            <div class="w-1/2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <select name="time" class="w-full pl-9 pr-3 py-2 border rounded text-sm">
                    <option value="">Pilih Waktu</option>
                    <option value="08:00">Pagi (08:00-12:00)</option>
                    <option value="13:00">Siang (13:00-16:00)</option>
                    <option value="17:00">Sore (17:00-20:00)</option>
                </select>
            </div>
        </div>
        <button id="submitButton" type="submit"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2.5 rounded font-semibold mt-2 transition-colors duration-300 flex items-center justify-center">
            <span id="buttonText">Cek Ketersediaan Jadwal</span>
            <svg id="buttonIcon" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
            <svg id="loadingIcon" class="ml-1 w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </button>
    </form>

    <div id="loadingIndicator" class="mt-4 hidden">
        <div class="flex items-center justify-center p-4">
            <div class="w-8 h-8 border-t-4 border-b-4 border-blue-500 rounded-full animate-spin"></div>
            <span class="ml-3 text-blue-500 font-medium">Sedang mencari jadwal tersedia...</span>
        </div>
    </div>

    <div id="scheduleResults" class="mt-4 hidden">
        <h4 class="text-lg font-medium text-gray-800">Hasil Pencarian</h4>
        <div id="scheduleData" class="mt-2 text-sm"></div>
    </div>

    <p class="text-xs text-gray-500 mt-3 text-center">
        *Jadwal tersedia akan ditampilkan berdasarkan pilihan Anda
    </p>
</div>

<script>
    document.getElementById('checkScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get the date input value to ensure we display it correctly in error messages
    const dateInput = this.querySelector('input[name="date"]');
    const selectedDate = dateInput?.value || new Date().toISOString().split('T')[0];
    
    // Get the time input
    const timeInput = this.querySelector('select[name="time"]');
    const selectedTime = timeInput?.value || '';
    
    // Show loading state
    const buttonText = document.getElementById('buttonText');
    const buttonIcon = document.getElementById('buttonIcon');
    const loadingIcon = document.getElementById('loadingIcon');
    const submitButton = document.getElementById('submitButton');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const scheduleResults = document.getElementById('scheduleResults');
    
    // Hide results if shown from previous search
    scheduleResults.classList.add('hidden');
    
    // Show loading state
    buttonText.textContent = 'Mencari...';
    buttonIcon.classList.add('hidden');
    loadingIcon.classList.remove('hidden');
    submitButton.disabled = true;
    submitButton.classList.add('opacity-75');
    loadingIndicator.classList.remove('hidden');
    
    // Create URL parameters - only send date and time
    const searchParams = new URLSearchParams();
    // Add form inputs to search params
    if (selectedDate) searchParams.append('date', selectedDate);
    if (selectedTime) searchParams.append('time', selectedTime);
    
    // Validation checks
    if (!selectedDate) {
        showError('Harap pilih tanggal terlebih dahulu');
        return;
    }
    
    if (!selectedTime) {
        showError('Harap pilih waktu terlebih dahulu');
        return;
    }
    
    // Helper function to show error
    function showError(message) {
        buttonText.textContent = 'Cek Ketersediaan Jadwal';
        buttonIcon.classList.remove('hidden');
        loadingIcon.classList.add('hidden');
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-75');
        loadingIndicator.classList.add('hidden');
        
        scheduleResults.classList.remove('hidden');
        document.getElementById('scheduleData').innerHTML = 
            `<div class="p-3 bg-red-50 border border-red-200 rounded-md">
                <p class="text-red-600">${message}</p>
                <p class="text-sm text-gray-500 mt-2">Silakan lengkapi form dan coba lagi.</p>
            </div>`;
    }
    // create validation date and time before sending request

    fetch(`{{ route('check-schedule') }}?${searchParams.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const dataDiv = document.getElementById('scheduleData');
            
            // Reset button state
            buttonText.textContent = 'Cek Ketersediaan Jadwal';
            buttonIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-75');
            loadingIndicator.classList.add('hidden');
            
            // Show results
            scheduleResults.classList.remove('hidden');
            
            
                let colorClass = data.status === 'available' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                let textColorClass = data.status === 'available' ? 'text-green-600' : 'text-red-600';
                
                let html = `<div class="p-3 ${colorClass} border rounded-md">
                    <p class="${textColorClass} font-medium">${data.message}</p>
                    <p class="text-gray-600">Tanggal: ${data.date}</p>
                    <p class="text-gray-600">Jam: ${data.time}</p>
                </div>`;
                
                if (data.status == 'available') {
                    html += `
                    <div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-md">
                        <h5 class="font-medium text-green-600">Jadwal Tersedia</h5>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div>
                                <p class="text-xs text-gray-500">Instruktur Tersedia:</p>
                                <ul class="list-disc list-inside">
                                    ${data.available_instructors.map(instructor => `<li>${instructor}</li>`).join('')}
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kendaraan Tersedia:</p>
                                <ul class="list-disc list-inside">
                                    ${data.available_cars.map(car => `<li>${car}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>`;
                } 
                
                dataDiv.innerHTML = html;
        
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            buttonText.textContent = 'Cek Ketersediaan Jadwal';
            buttonIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-75');
            loadingIndicator.classList.add('hidden');
            
            // Show error message with the selected date
            document.getElementById('scheduleResults').classList.remove('hidden');
            document.getElementById('scheduleData').innerHTML = 
                '<div class="p-3 bg-red-50 border border-red-200 rounded-md">' +
                '<p class="text-red-600">Terjadi kesalahan saat memeriksa jadwal.</p>' +
                `<p class="text-gray-600">Tanggal: ${selectedDate}</p>` +
                '<p class="text-sm text-gray-500 mt-2">Silakan coba lagi nanti.</p>' +
                '</div>';
        });
});
</script>