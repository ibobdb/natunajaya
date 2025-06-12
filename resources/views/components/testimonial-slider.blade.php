<!-- Testimonial Slider Component -->
<div class="relative px-4 md:px-10">
    <!-- Testimonial Slider Container -->
    <div class="testimonial-slider overflow-hidden relative">
        <div class="testimonial-slide-container flex transition-transform duration-500 ease-in-out">
            @forelse($testimonials as $testimonial)
            <!-- Dynamic Testimonial -->
            <div
                class="testimonial-slide bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow flex-shrink-0 w-full md:w-1/3 mx-2">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 overflow-hidden">
                        @if ($testimonial->user && $testimonial->user->profile_photo_path)
                        <img src="{{ Storage::url($testimonial->user->profile_photo_path) }}" alt="Testimonial"
                            class="h-full w-full object-cover" />
                        @else
                        <div
                            class="h-full w-full flex items-center justify-center bg-blue-500 text-white font-bold text-xl">
                            {{ $testimonial->user ? substr($testimonial->user->name, 0, 1) : 'U' }}
                        </div>
                        @endif
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-lg">
                            {{ $testimonial->user ? $testimonial->user->name : 'Anonymous' }}
                        </h4>
                        <p class="text-gray-600 text-sm">Siswa
                            {{ $testimonial->user && $testimonial->user->student ? 'SIM ' . $testimonial->user->student->license_type : 'Kursus' }}
                        </p>
                    </div>
                </div>
                <div class="flex mb-3">
                    @for($i = 1; $i <= 5; $i++) <svg
                        class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                        </path>
                        </svg>
                        @endfor
                </div>
                <p class="text-gray-700 italic">
                    "{{ $testimonial->content }}"
                </p>
            </div>
            @empty
            <!-- Fallback Testimonials if no records found -->
            <div
                class="testimonial-slide bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow flex-shrink-0 w-full md:w-1/3 mx-2">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Testimonial"
                            class="h-full w-full object-cover" />
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-lg">Siti Nuraini</h4>
                        <p class="text-gray-600 text-sm">Siswa SIM A</p>
                    </div>
                </div>
                <div class="flex mb-3">
                    @for($i = 1; $i <= 5; $i++) <svg class="w-5 h-5 {{ $i <= 4 ? 'text-yellow-400' : 'text-gray-300' }}"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                        </path>
                        </svg>
                        @endfor
                </div>
                <p class="text-gray-700 italic">
                    "Saya sangat puas dengan kursus mengemudi di Natuna. Instruktur sangat sabar dan profesional.
                    Dalam waktu singkat saya sudah bisa mengemudi dengan percaya diri dan lulus ujian SIM pertama
                    kali!"
                </p>
            </div>

            <!-- Testimonial 2 -->
            <div
                class="testimonial-slide bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow flex-shrink-0 w-full md:w-1/3 mx-2">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/men/57.jpg" alt="Testimonial"
                            class="h-full w-full object-cover" />
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-lg">Budi Santoso</h4>
                        <p class="text-gray-600 text-sm">Siswa SIM C</p>
                    </div>
                </div>
                <div class="flex mb-3">
                    @for($i = 1; $i <= 5; $i++) <svg class="w-5 h-5 {{ $i <= 4 ? 'text-yellow-400' : 'text-gray-300' }}"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                        </path>
                        </svg>
                        @endfor
                </div>
                <p class="text-gray-700 italic">
                    "Fasilitas dan kendaraan untuk latihan sangat bagus dan terawat. Instrukturnya juga sangat
                    kompeten dalam mengajarkan teknik berkendara yang aman. Rekomendasi banget!"
                </p>
            </div>

            <!-- Testimonial 3 -->
            <div
                class="testimonial-slide bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow flex-shrink-0 w-full md:w-1/3 mx-2">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="Testimonial"
                            class="h-full w-full object-cover" />
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-lg">Dewi Lestari</h4>
                        <p class="text-gray-600 text-sm">Siswa Paket Profesional</p>
                    </div>
                </div>
                <div class="flex mb-3">
                    @for($i = 1; $i <= 5; $i++) <svg class="w-5 h-5 {{ $i <= 5 ? 'text-yellow-400' : 'text-gray-300' }}"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                        </path>
                        </svg>
                        @endfor
                </div>
                <p class="text-gray-700 italic">
                    "Saya mengambil paket profesional dan sangat terkesan dengan metode pengajaran yang
                    komprehensif. Sekarang saya lebih percaya diri mengemudi di segala kondisi jalan dan cuaca."
                </p>
            </div>
            @endforelse
        </div>

        <!-- Navigation Buttons -->
        <button
            class="testimonial-prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-md z-10 focus:outline-none hover:bg-gray-100">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button
            class="testimonial-next absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-md z-10 focus:outline-none hover:bg-gray-100">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    <!-- Dots Indicator -->
    <div class="flex justify-center mt-6 space-x-2 testimonial-dots">
        <!-- Dots will be dynamically created by JavaScript -->
    </div>
</div>

<!-- JavaScript for Testimonial Slider -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slideContainer = document.querySelector('.testimonial-slide-container');
        const slides = document.querySelectorAll('.testimonial-slide');
        const prevButton = document.querySelector('.testimonial-prev');
        const nextButton = document.querySelector('.testimonial-next');
        const dotsContainer = document.querySelector('.testimonial-dots');
        
        let currentIndex = 0;
        let slideWidth;
        let maxIndex;
        let autoSlideInterval;
        
        // Create dots
        function createDots() {
            dotsContainer.innerHTML = '';
            const totalDots = window.innerWidth < 768 ? slides.length : Math.ceil(slides.length / 3);
            maxIndex = window.innerWidth < 768 ? slides.length - 1 : Math.max(0, Math.ceil(slides.length / 3) - 1);
            
            for (let i = 0; i < totalDots; i++) {
                const dot = document.createElement('button');
                dot.classList.add('w-3', 'h-3', 'rounded-full', 'bg-gray-300', 'focus:outline-none');
                if (i === 0) {
                    dot.classList.add('bg-blue-500');
                }
                dot.onclick = () => goToSlide(i);
                dotsContainer.appendChild(dot);
            }
        }
        
        // Update slide position
        function updateSlidePosition() {
            let translateValue;
            if (window.innerWidth < 768) {
                // Mobile: Show one slide at a time
                translateValue = -currentIndex * (slides[0].offsetWidth + 16); // Slide width + margin
            } else {
                // Desktop: Show three slides at a time
                const visibleSlides = 3;
                const step = Math.min(visibleSlides, slides.length - visibleSlides * currentIndex);
                translateValue = -currentIndex * slides[0].offsetWidth * step;
            }
            slideContainer.style.transform = `translateX(${translateValue}px)`;
            
            // Update active dot
            const dots = document.querySelectorAll('.testimonial-dots button');
            dots.forEach((dot, i) => {
                if (i === currentIndex) {
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-blue-500');
                } else {
                    dot.classList.remove('bg-blue-500');
                    dot.classList.add('bg-gray-300');
                }
            });
        }
        
        // Go to specific slide
        function goToSlide(index) {
            currentIndex = Math.max(0, Math.min(index, maxIndex));
            updateSlidePosition();
        }
        
        // Go to next slide
        function goToNextSlide() {
            currentIndex = (currentIndex + 1) > maxIndex ? 0 : currentIndex + 1;
            updateSlidePosition();
        }
        
        // Go to previous slide
        function goToPrevSlide() {
            currentIndex = (currentIndex - 1) < 0 ? maxIndex : currentIndex - 1;
            updateSlidePosition();
        }
        
        // Initialize slider
        function initSlider() {
            // Set initial slide width and container
            slides.forEach(slide => {
                if (window.innerWidth < 768) {
                    slide.style.width = 'calc(100% - 16px)'; // Full width with margin on mobile
                } else {
                    slide.style.width = 'calc(33.333% - 16px)'; // 1/3 width with margin on desktop
                }
            });
            
            createDots();
            updateSlidePosition();
            
            // Start auto-sliding
            if (autoSlideInterval) clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(goToNextSlide, 5000);
        }
        
        // Event listeners
        prevButton.addEventListener('click', () => {
            goToPrevSlide();
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(goToNextSlide, 5000);
        });
        
        nextButton.addEventListener('click', () => {
            goToNextSlide();
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(goToNextSlide, 5000);
        });
        
        // Handle resize to make it responsive
        window.addEventListener('resize', () => {
            clearInterval(autoSlideInterval);
            initSlider();
        });
        
        // Initialize
        initSlider();
    });
</script>