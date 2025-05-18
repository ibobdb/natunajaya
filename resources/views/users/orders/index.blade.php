<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          ORDERS
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            Create Order
            <form action="{{ route('orders.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label for="customer_id" class="block text-gray-700 text-sm font-bold mb-2">Customer</label>
                    <input type="text" id="customer_id" name="customer_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" disabled value="{{ Auth::user()->name }}">
                </div>
                <div class="mb-4">
                    <label for="order_date" class="block text-gray-700 text-sm font-bold mb-2">Order Date</label>
                    <input type="datetime-local" id="order_date" name="order_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="{{ date('Y-m-d\TH:i') }}">
                </div>
                <div class="mb-4">
                    <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2">Course:</label>
                    <select id="product_id" name="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                      <option value="">Select a course</option>
                      @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                      @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="teacher_id" class="block text-gray-700 text-sm font-bold mb-2">Instructor:</label>
                    <select id="teacher_id" name="teacher_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required disabled>
                      <option value="">Select a course first</option>
                    </select>
                </div>
                <div class="mb-4">
                    <div class="mb-4">
                        <label for="transmission_type" class="block text-gray-700 text-sm font-bold mb-2">Transmission Type:</label>
                        <select id="transmission_type" name="transmission_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                          <option value="">Select transmission type</option>
                          <option value="manual">Manual</option>
                          <option value="matic">Matic</option>
                        </select>
                      </div>
                    <label for="car_id" class="block text-gray-700 text-sm font-bold mb-2">Mobil:</label>
                    <select id="car_id" name="car_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                      <option value="">Select a car</option>
                      @foreach($cars as $car)
                        <option value="{{ $car->id }}">{{ $car->name }}</option>
                      @endforeach
                    </select>
                </div>
                
                <!-- Hidden field to track if availability has been checked -->
                <input type="hidden" id="availability_checked" name="availability_checked" value="0">
                
                <div id="availability_result" class="mb-4 p-3 hidden rounded-lg border">
                  <!-- Availability result will be displayed here -->
                </div>
                
                <div class="flex justify-between items-center mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                  <span class="font-medium text-gray-700">Check availability of instructor and vehicle</span>
                  <button id="check_availability_btn" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition duration-150 ease-in-out flex items-center" type="button">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  Check Availability
                  </button>
                </div>
                
                <button id="submit_order_btn" type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" disabled>
                    Create Order
                </button>
            </form>

            </div>
        </div>
    </div>

    <script>
        // Define global variables so they're accessible to all functions
        let orderDateInput, productSelect, carSelect, transmissionSelect, teacherSelect, availabilityResult;
        let originalCars = []; // Added to global scope
        
        document.addEventListener('DOMContentLoaded', function() {
          // Initialize the global variables
          orderDateInput = document.getElementById('order_date');
          productSelect = document.getElementById('product_id');
          carSelect = document.getElementById('car_id');
          transmissionSelect = document.getElementById('transmission_type');
          teacherSelect = document.getElementById('teacher_id');
          availabilityResult = document.getElementById('availability_result');
          
          // Set minimum booking time (current time + 12 hours)
          const now = new Date();
          const minBookingTime = new Date(now.getTime() + (12 * 60 * 60 * 1000)); // Add 12 hours
          
          // Format to YYYY-MM-DDThh:mm
          const minYear = minBookingTime.getFullYear();
          const minMonth = String(minBookingTime.getMonth() + 1).padStart(2, '0');
          const minDay = String(minBookingTime.getDate()).padStart(2, '0');
          const minHours = String(minBookingTime.getHours()).padStart(2, '0');
          const minMinutes = String(minBookingTime.getMinutes()).padStart(2, '0');
          const formattedMinTime = `${minYear}-${minMonth}-${minDay}T${minHours}:${minMinutes}`;
          
          orderDateInput.min = formattedMinTime;
          
          // If current value is less than minimum, update it
          if (new Date(orderDateInput.value) < minBookingTime) {
            orderDateInput.value = formattedMinTime;
          }
          
          // Store original cars for filtering - FIXED: removed redefinition of transmissionSelect and carSelect
          originalCars = [...carSelect.options].map(option => ({
            value: option.value,
            text: option.textContent
          }));
          
          transmissionSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // If no transmission type is selected, restore all cars
            if (!selectedType) {
              carSelect.innerHTML = '';
              originalCars.forEach(car => {
                const option = document.createElement('option');
                option.value = car.value;
                option.textContent = car.text;
                carSelect.appendChild(option);
              });
              return;
            }
            
            // Clear current car options
            carSelect.innerHTML = '<option value="">Loading cars...</option>';
            
            // Fetch filtered cars based on transmission type
            fetch(`{{ route('cars.filter') }}?type=${selectedType}`, {
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
              }
              return response.json();
            })
            .then(cars => {
              carSelect.innerHTML = '<option value="">Select a car</option>';
              if (cars.length === 0) {
                carSelect.innerHTML += '<option value="" disabled>No cars available for this transmission type</option>';
              } else {
                cars.forEach(car => {
                  const option = document.createElement('option');
                  option.value = car.id;
                  option.textContent = car.name;
                  carSelect.appendChild(option);
                });
              }
            })
            .catch(error => {
              console.error('Error fetching cars:', error);
              carSelect.innerHTML = '<option value="">Error loading cars. Please try again.</option>';
            });
          });
        });
        
        // Availability checking functionality
        document.addEventListener('DOMContentLoaded', function() {
            const checkAvailabilityBtn = document.getElementById('check_availability_btn');
            const submitOrderBtn = document.getElementById('submit_order_btn');
            const availabilityChecked = document.getElementById('availability_checked');
            
            // We don't need to redefine these variables since we're using the globals
            // Just use the global variables directly
            
            // Reset availability status when any relevant field changes
            [orderDateInput, productSelect, carSelect, transmissionSelect, teacherSelect].forEach(element => {
                element.addEventListener('change', function() {
                    availabilityChecked.value = "0";
                    availabilityResult.classList.add('hidden');
                    submitOrderBtn.disabled = true;
                    submitOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
                });
            });
            
            checkAvailabilityBtn.addEventListener('click', function() {
                // Validate all required fields are filled
                if (!validateFields()) {
                    return;
                }
                
                // Parse the date to match expected format
                const orderDate = new Date(orderDateInput.value);
                const formattedDate = orderDate.toISOString().slice(0, 10);
                const formattedTime = orderDate.toTimeString().slice(0, 8);
                
                // Prepare form data for availability check
                const formData = new FormData();
                formData.append('date', formattedDate);
                formData.append('time', formattedTime);
                formData.append('car_id', carSelect.value);
                formData.append('teacher_id', productSelect.value);
                formData.append('_token', '{{ csrf_token() }}');
                
                // Verify that selected time is at least 12 hours in the future
                const now = new Date();
                const minBookingTime = new Date(now.getTime() + (12 * 60 * 60 * 1000)); // Add 12 hours
                
                if (orderDate < minBookingTime) {
                    availabilityResult.classList.remove('hidden');
                    availabilityResult.className = 'mb-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-200';
                    availabilityResult.innerHTML = `<div class="flex items-center"><svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>Please select a time at least 12 hours from now.</div>`;
                    return;
                }
                
                // Disable the check button and show loading state
                checkAvailabilityBtn.disabled = true;
                checkAvailabilityBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Checking...';
                
                // Make the availability check request using FormData instead of JSON
                fetch('{{ route('orders.check-availability') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    // Log the response for debugging
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(result => {
                    // Reset the check button
                    checkAvailabilityBtn.disabled = false;
                    checkAvailabilityBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg> Check Availability';
                    
                    // Show availability result
                    availabilityResult.classList.remove('hidden');
                    
                    if (result.available) {
                        availabilityResult.className = 'mb-4 p-3 bg-green-100 text-green-800 rounded-lg border border-green-200';
                        availabilityResult.innerHTML = '<div class="flex items-center"><svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>Available! Car and instructor are available at the selected time.</div>';
                        
                        // Enable submit button
                        availabilityChecked.value = "1";
                        submitOrderBtn.disabled = false;
                        submitOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    } else {
                        availabilityResult.className = 'mb-4 p-3 bg-red-100 text-red-800 rounded-lg border border-red-200';
                        
                        let message = '<div class="flex items-center"><svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>Not Available! ';
                        
                        if (!result.car_available && !result.teacher_available) {
                            message += 'Both car and instructor are not available at the selected time.';
                        } else if (!result.car_available) {
                            message += 'The selected car is not available at the selected time.';
                        } else {
                            message += 'The instructor is not available at the selected time.';
                        }
                        
                        message += '</div>';
                        availabilityResult.innerHTML = message;
                        
                        // Keep submit button disabled
                        submitOrderBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error checking availability:', error);
                    
                    // Reset the check button
                    checkAvailabilityBtn.disabled = false;
                    checkAvailabilityBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg> Check Availability';
                    
                    // Show error
                    availabilityResult.classList.remove('hidden');
                    availabilityResult.className = 'mb-4 p-3 bg-red-100 text-red-800 rounded-lg border border-red-200';
                    availabilityResult.innerHTML = '<div class="flex items-center"><svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>Error checking availability. Please try again.</div>';
                });
            });
            
            // Load teachers based on selected course
            productSelect.addEventListener('change', function() {
                const courseId = this.value;
                
                // Clear and disable teacher select if no course is selected
                if (!courseId) {
                    teacherSelect.innerHTML = '<option value="">Select a course first</option>';
                    teacherSelect.disabled = true;
                    return;
                }
                
                teacherSelect.disabled = true;
                teacherSelect.innerHTML = '<option value="">Loading instructors...</option>';
                
                // Fetch teachers based on selected course
                fetch(`{{ route('teachers.filter') }}?course_id=${courseId}`, {
                  headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                  }
                })
                .then(response => {
                  if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                  }
                  return response.json();
                })
                .then(teachers => {
                  teacherSelect.innerHTML = '<option value="">Select an instructor</option>';
                  if (teachers.length === 0) {
                    teacherSelect.innerHTML += '<option value="" disabled>No instructors available for this course</option>';
                  } else {
                    teachers.forEach(teacher => {
                      const option = document.createElement('option');
                      option.value = teacher.id;
                      option.textContent = teacher.name;
                      teacherSelect.appendChild(option);
                    });
                  }
                  teacherSelect.disabled = false;
                })
                .catch(error => {
                  console.error('Error fetching teachers:', error);
                  teacherSelect.innerHTML = '<option value="">Error loading instructors. Please try again.</option>';
                  teacherSelect.disabled = true;
                });
            });
        });
        
        // Validate that all required fields are filled
        function validateFields() {
            const emptyFields = [];
            
            if (!orderDateInput.value) emptyFields.push('Order Date');
            if (!productSelect.value) emptyFields.push('Course');
            if (!transmissionSelect.value) emptyFields.push('Transmission Type');
            if (!carSelect.value) emptyFields.push('Car');
            if (!teacherSelect.value) emptyFields.push('Instructor');
            
            if (emptyFields.length > 0) {
                availabilityResult.classList.remove('hidden');
                availabilityResult.className = 'mb-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-200';
                availabilityResult.innerHTML = `<div class="flex items-center"><svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>Please fill in the following fields before checking availability: ${emptyFields.join(', ')}</div>`;
                return false;
            }
            
            return true;
        }
        
        // Prevent form submission if availability has not been checked
        document.querySelector('form').addEventListener('submit', function(event) {
            if (availabilityChecked.value !== "1") {
                event.preventDefault();
                
                availabilityResult.classList.remove('hidden');
                availabilityResult.className = 'mb-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-200';
                availabilityResult.innerHTML = '<div class="flex items-center"><svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>Please check availability before creating the order.</div>';
            }
        });
    </script>
</x-app-layout>
