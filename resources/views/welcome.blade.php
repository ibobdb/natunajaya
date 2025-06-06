<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ env('APP_NAME', 'Natuna Driving Academy') }}</title>


	<!-- Fade-in Animation CSS -->
	<style>
		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.page-content {
			opacity: 0;
			animation: fadeIn 0.8s ease-in-out forwards;
		}

		.staggered-fade-in>* {
			opacity: 0;
			animation: fadeIn 0.5s ease-in-out forwards;
		}

		.delay-100 {
			animation-delay: 0.1s;
		}

		.delay-200 {
			animation-delay: 0.2s;
		}

		.delay-300 {
			animation-delay: 0.3s;
		}

		.delay-400 {
			animation-delay: 0.4s;
		}

		.delay-500 {
			animation-delay: 0.5s;
		}

		.delay-600 {
			animation-delay: 0.6s;
		}

		.delay-700 {
			animation-delay: 0.7s;
		}

		.delay-800 {
			animation-delay: 0.8s;
		}
	</style>

	@vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="opacity-0 transition-opacity duration-500" onload="document.body.classList.add('opacity-100')">
	<section class="relative h-[600px] flex items-center bg-cover bg-center"
		style="background-image: url('{{ asset('assets/img/a.jpg') }}');">
		<div class="absolute inset-0 bg-black bg-opacity-50"></div>
		<!-- Navbar -->
		<nav class="absolute top-0 left-0 w-full flex justify-between items-center px-8 py-4 z-20 staggered-fade-in">
			<div class="flex items-center space-x-2 delay-100">
				<img src="{{ asset('assets/img/logo.png') }}" alt="Logo Natuna" class="h-12 filter drop-shadow-md" />
			</div>
			<ul class="hidden md:flex space-x-8 delay-200">

				<li><a href="#kursus" class="text-white font-medium hover:text-blue-300 transition-colors">Program
						Kursus</a></li>
				<li><a href="#biaya" class="text-white font-medium hover:text-blue-300 transition-colors">Biaya
						Kursus</a></li>
				<li><a href="#kontak" class="text-white font-medium hover:text-blue-300 transition-colors">Kontak</a>
				</li>
			</ul>
			@if (Auth::check())
			<div class="flex items-center delay-300">
				<a href="{{ route('dashboard') }}"
					class="flex items-center space-x-2 bg-white text-blue-600 hover:bg-blue-50 transition-colors duration-200 px-5 py-2 rounded-md font-medium border border-blue-200 shadow-md">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
						<path
							d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
					</svg>
					<span>Dasbor</span>
				</a>
			</div>
			@else
			<div class="flex items-center delay-300">
				<a href="{{ route('login') }}"
					class="text-white font-medium mr-4 hover:text-blue-200 transition-colors">Masuk</a>
				<a href="{{ route('register') }}"
					class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-5 py-2 rounded font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
					Daftar
				</a>
			</div>
			@endif
		</nav>

		<div
			class="relative z-10 flex flex-col md:flex-row items-center w-full max-w-7xl mx-auto px-6 staggered-fade-in">
			<!-- Left Section -->
			<div class="flex-1 text-white mt-24 md:mt-0 delay-400">
				<div class="inline-flex items-center bg-blue-600 bg-opacity-70 rounded-full px-4 py-1.5 mb-3">
					<span class="text-white text-sm font-medium">Lebih dari 1.000 siswa telah lulus bersama kami</span>
				</div>
				<h1 class="text-4xl md:text-5xl font-bold mt-4 leading-tight text-shadow-lg">
					Pelatihan Mengemudi<br> Profesional Terbaik.
				</h1>
				<p class="mt-6 max-w-lg text-lg text-white leading-relaxed">
					Partner terpercaya untuk pendidikan pengemudi komprehensif. Kami menawarkan kursus tersertifikasi,
					instruktur berpengalaman, dan pelatihan praktis untuk pemula maupun profesional.
				</p>
				<div class="flex items-center mt-8 space-x-4">
					<a href="{{ route('register') }}"
						class="bg-gradient-to-r from-blue-600 to-blue-400 px-8 py-3 rounded text-white font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 flex items-center">
						<span>Daftar Sekarang</span>
						<svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd"
								d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
								clip-rule="evenodd"></path>
						</svg>
					</a>
					<a href="#kursus"
						class="px-6 py-3 border-2 border-white text-white font-semibold rounded hover:bg-white hover:text-blue-600 transition-colors duration-300">
						Lihat Program
					</a>
				</div>
			</div>

			<!-- Right Section (Schedule Check Card) -->
			<div class="flex-1 flex justify-center mt-12 md:mt-0 delay-500">
				<x-check-schedule :courses="$courses" :cars="$cars" />
			</div>
		</div>
	</section>

	<style>
		.text-shadow-lg {
			text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
		}
	</style>

	<!-- Program Kursus Section -->
	<section id="kursus" class="py-16 bg-gray-50">
		<div class="w-full max-w-7xl mx-auto px-6">
			<div class="text-center mb-12">
				<span class="text-blue-600 text-sm font-medium">Program Unggulan Kami</span>
				<h2 class="text-3xl md:text-4xl font-bold mt-2">Paket Promo</h2>
				<p class="mt-4 max-w-2xl mx-auto text-gray-600">
					Pilih program kursus yang sesuai dengan kebutuhan Anda. Semua program kami dirancang oleh instruktur
					berpengalaman dan tersertifikasi.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
				<!-- Kursus SIM A -->
				<div
					class="bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl flex flex-col h-full">
					<div class="h-48 bg-cover bg-center relative"
						style="background-image: url('{{ asset('assets/img/sima.jpeg') }}')">
						<div
							class="absolute bottom-0 left-0 bg-gradient-to-r from-blue-600 to-blue-400 text-white text-xs font-bold px-4 py-1">
							Manual & Automatic
						</div>
					</div>
					<div class="p-6 flex flex-col flex-grow">
						<div class="flex justify-between items-center mb-2">
							<h3 class="text-xl font-semibold text-gray-900">Kursus SIM A</h3>
							<div class="flex">
								<div class="text-xs bg-blue-100 text-blue-800 rounded-full px-2 py-1 flex items-center">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
										viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
											d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
									12 x 1 Jam
								</div>
							</div>
						</div>
						<div class="flex items-center mt-1 mb-3">
							<span
								class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Pemula</span>
							<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded ml-2">12
								Pertemuan</span>
						</div>
						<p class="text-gray-600 text-sm mb-4">
							Kursus mengemudi untuk pemula yang ingin mendapatkan SIM A. Termasuk teori dan praktek
							mengemudi dengan mobil manual atau matic.
						</p>
						<ul class="mb-4 space-y-2">
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Durasi: 1 jam per pertemuan
							</li>
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Instruktur bersertifikasi
							</li>
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Mendapatkan SIM A
							</li>
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Jadwal Senin - Jumat, 08.00 - 20.00
							</li>

						</ul>
						<div class="mt-auto flex justify-between items-center">
							<div class="flex flex-col">
								<span class="text-lg font-bold text-blue-600">Rp 2.200.000</span>
							</div>
							<button
								class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium text-sm transition-colors duration-300">
								Daftar Sekarang
							</button>
						</div>
					</div>
				</div>

				<!-- Kursus SIM C -->
				<div
					class="bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl flex flex-col h-full">
					<div class="h-48 bg-cover bg-center relative"
						style="background-image: url('{{ asset('assets/img/simb.jpg') }}')">
						<div
							class="absolute bottom-0 left-0 bg-gradient-to-r from-blue-600 to-blue-400 text-white text-xs font-bold px-4 py-1">
							Manual & Automatic
						</div>
					</div>
					<div class="p-6 flex flex-col flex-grow">
						<div class="flex justify-between items-center mb-2">
							<h3 class="text-xl font-semibold text-gray-900">Kursus SIM A</h3>
							<div class="flex">
								<div class="text-xs bg-blue-100 text-blue-800 rounded-full px-2 py-1 flex items-center">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
										viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
											d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
									5 x 2 Jam
								</div>
							</div>
						</div>
						<div class="flex items-center mt-1 mb-3">
							<span
								class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Pemula</span>
							<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded ml-2">5
								Pertemuan</span>
							<span
								class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded ml-2">Favorit</span>
						</div>
						<p class="text-gray-600 text-sm mb-4">
							Kursus mengemudi sepeda motor untuk pemula. Meliputi teknik berkendara yang aman, manuver
							dasar, dan persiapan ujian SIM A.
						</p>
						<ul class="mb-4 space-y-2">
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Durasi: 2 jam per pertemuan
							</li>
							</li>
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Instruktur berpengalaman
							</li>
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Mendapatkan SIM A
							</li>
							<li class="flex items-center text-sm text-gray-600">
								<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
								Minggu 08:00 - 16:00
							</li>
						</ul>
						<div class="mt-auto flex justify-between items-center">
							<div class="flex flex-col">
								<span class="text-lg font-bold text-blue-600">Rp 2.200.000</span>
							</div>
							<button
								class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white rounded font-medium text-sm transition-colors duration-300">
								Daftar Sekarang
							</button>
						</div>
					</div>
				</div>

			</div>

			<div class="text-center mt-10 flex flex-col items-center">
				<div
					class="inline-flex items-center justify-center bg-blue-50 text-blue-600 text-sm px-4 py-2 rounded-full mb-6">
					<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"
						xmlns="http://www.w3.org/2000/svg">
						<path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
					</svg>
					Semua kursus termasuk materi pembelajaran dan latihan praktek
				</div>
				<a href="/semua-kursus" class="inline-flex items-center font-medium text-blue-600 hover:text-blue-800">
					Lihat Semua Program Kursus
					<svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20"
						xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd"
							d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
							clip-rule="evenodd"></path>
					</svg>
				</a>
			</div>
		</div>
	</section>

	<!-- Biaya Kursus Section -->
	<section id="biaya" class="py-16 bg-white">
		<div class="w-full max-w-7xl mx-auto px-6">
			<div class="text-center mb-12">
				<span class="text-blue-600 text-sm font-medium">Pilihan Harga Terbaik</span>
				<h2 class="text-3xl md:text-4xl font-bold mt-2">Biaya Kursus Mengemudi</h2>
				<p class="mt-4 max-w-2xl mx-auto text-gray-600">
					Kami menawarkan paket harga yang kompetitif dan transparan, dengan beberapa opsi yang dapat
					disesuaikan dengan kebutuhan dan anggaran Anda.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<!-- Paket Dasar -->
				<div
					class="bg-white rounded-lg border border-gray-200 shadow-lg p-8 flex flex-col transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
					<h3 class="text-xl font-bold text-gray-900 mb-2">Paket A</h3>
					<p class="text-gray-600 text-sm mb-6">Ideal untuk pemula yang ingin belajar dasar-dasar mengemudi
					</p>
					<div class="mb-6">
						<span class="text-4xl font-bold text-blue-600">Rp 680.000</span>

					</div>
					<ul class="mb-6 space-y-4 flex-grow">
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700"><strong>Senin - Jumat</strong> 08:00 - 16:00</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700">Pilihan Matic / Manual</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 005.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Teori dasar mengemudi</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Sertifikat pelatihan</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700">Buku panduan mengemudi</span>
						</li>
					</ul>
					<button
						class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-md font-medium transition-colors focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50">
						Pilih Paket
					</button>
				</div>

				<!-- Paket Standar -->
				<div
					class="bg-blue-50 rounded-lg border-2 border-blue-500 shadow-xl p-8 flex flex-col relative -translate-y-4 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
					<div
						class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-blue-600 to-blue-400 text-white px-6 py-1 rounded-full text-sm font-semibold shadow-md animate-pulse">
						Terpopuler
					</div>
					<h3 class="text-xl font-bold text-gray-900 mb-2">Paket B</h3>
					<p class="text-gray-600 text-sm mb-6">Kursus lengkap untuk persiapan ujian SIM</p>
					<div class="mb-6">
						<span class="text-4xl font-bold text-blue-600">Rp 925.000</span>

					</div>
					<ul class="mb-6 space-y-4 flex-grow">
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700"><strong>Minggu / Tanggal Merah</strong> 08:00- 16:00</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700"><strong>Pilihan Matic / Manual</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 005.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Teori mengemudi lengkap</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M4 4a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H4zM4 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H4zM11 4a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V4zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Simulasi ujian SIM</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Konsultasi gratis selama 1 bulan</span>
						</li>
					</ul>
					<button
						class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-md font-medium transition-colors focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50">
						Pilih Paket
					</button>
				</div>
				<!-- Paket Dasar -->
				<div
					class="bg-white rounded-lg border border-gray-200 shadow-lg p-8 flex flex-col transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
					<h3 class="text-xl font-bold text-gray-900 mb-2">Paket C</h3>
					<p class="text-gray-600 text-sm mb-6">Ideal untuk pemula yang ingin belajar dasar-dasar mengemudi
						matic dan manual
					</p>
					<div class="mb-6">
						<span class="text-4xl font-bold text-blue-600">Rp 950.000</span>

					</div>
					<ul class="mb-6 space-y-4 flex-grow">
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700"><strong>Pilihan Senin - Jumat Atau Minggu</strong> 08:00 -
								16:00</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 005.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Teori dasar mengemudi</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z">
									</path>
								</svg>
							</div>
							<span class="text-gray-700">Sertifikat pelatihan</span>
						</li>
						<li class="flex items-center">
							<div
								class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
								<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
									xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
										clip-rule="evenodd"></path>
								</svg>
							</div>
							<span class="text-gray-700">Buku panduan mengemudi</span>
						</li>
					</ul>
					<button
						class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-md font-medium transition-colors focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50">
						Pilih Paket
					</button>
				</div>
			</div>

		</div>
	</section>

	<!-- How to Section -->
	<section class="py-16 bg-gray-100">
		<div class="w-full max-w-7xl mx-auto px-6">
			<div class="text-center mb-12">
				<span class="text-blue-600 text-sm font-medium">Langkah-Langkah Mendaftar</span>
				<h2 class="text-3xl md:text-4xl font-bold mt-2">Cara Mendaftar Kursus</h2>
				<p class="mt-4 max-w-2xl mx-auto text-gray-600">
					Proses pendaftaran yang mudah dan cepat. Ikuti langkah-langkah berikut untuk memulai pelatihan
					mengemudi Anda bersama kami.
				</p>
			</div>

			<!-- Progress bar -->
			<div class="hidden md:block w-full max-w-5xl mx-auto mb-8 px-12">
				<div class="h-1 w-full bg-gray-300 rounded-full relative">
					<div class="h-1 bg-blue-500 rounded-full w-1/4" id="registration-progress"></div>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
				<!-- Connection lines for desktop -->
				<div class="hidden md:block absolute top-20 left-[25%] w-[50%] h-0.5 bg-blue-200 z-0"></div>

				<!-- Step 1 -->
				<div
					class="bg-white rounded-lg shadow-lg p-6 text-center relative hover:shadow-xl hover:transform hover:scale-105 transition-all duration-300 z-10">
					<div
						class="absolute -top-3 left-0 right-0 mx-auto w-24 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
						Langkah Pertama
					</div>
					<div
						class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold mx-auto mb-4 border-4 border-blue-100">
						1
					</div>
					<div class="h-14">
						<h3 class="text-lg font-bold mb-1">Daftar & Lengkapi Data</h3>
						<p class="text-gray-500 text-xs">
							Isi formulir pendaftaran dan lengkapi data diri Anda.
						</p>
					</div>
					<div class="my-4 h-16">
						<div class="flex items-center mb-2 text-sm text-gray-600">
							<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd"
									d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
									clip-rule="evenodd"></path>
							</svg>
							<span>Pendaftaran online 24 jam</span>
						</div>
						<div class="flex items-center text-sm text-gray-600">
							<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd"
									d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
									clip-rule="evenodd"></path>
							</svg>
							<span>Verifikasi cepat</span>
						</div>
					</div>
					{{-- <div class="space-y-3 mt-4">
						<a href="{{ route('register') }}"
					class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium
					transition-colors">
					Daftar Sekarang
					</a>
					<a href="#faq" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Pelajari lebih
						lanjut</a>
				</div> --}}
			</div>

			<!-- Step 2 -->
			<div
				class="bg-white rounded-lg shadow-lg p-6 text-center relative hover:shadow-xl hover:transform hover:scale-105 transition-all duration-300 z-10">
				<div
					class="absolute -top-3 left-0 right-0 mx-auto w-24 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
					Langkah Kedua
				</div>
				<div
					class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold mx-auto mb-4 border-4 border-blue-100">
					2
				</div>
				<div class="h-14">
					<h3 class="text-lg font-bold mb-1">Cek Jadwal</h3>
					<p class="text-gray-500 text-xs">
						Pilih jadwal pelatihan yang sesuai waktu Anda.
					</p>
				</div>
				<div class="my-4 h-16">
					<div class="flex items-center mb-2 text-sm text-gray-600">
						<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
								clip-rule="evenodd"></path>
						</svg>
						<span>Jadwal fleksibel</span>
					</div>
					<div class="flex items-center text-sm text-gray-600">
						<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
								clip-rule="evenodd"></path>
						</svg>
						<span>Pilihan instruktur</span>
					</div>
				</div>
				{{-- <div class="space-y-3 mt-4">
					<button
						class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center justify-center">
						<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd"
								d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4a.5.5 0 01-.5-.5V4a.5.5 0 01.5-.5H6z"
								clip-rule="evenodd"></path>
						</svg>
						Lihat Jadwal
					</button>
					<a href="#jadwal" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Pelajari
						lebih lanjut</a>
				</div> --}}
			</div>

			<!-- Step 3 -->
			<div
				class="bg-white rounded-lg shadow-lg p-6 text-center relative hover:shadow-xl hover:transform hover:scale-105 transition-all duration-300 z-10">
				<div
					class="absolute -top-3 left-0 right-0 mx-auto w-24 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
					Langkah Ketiga
				</div>
				<div
					class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold mx-auto mb-4 border-4 border-blue-100">
					3
				</div>
				<div class="h-14">
					<h3 class="text-lg font-bold mb-1">Pembayaran</h3>
					<p class="text-gray-500 text-xs">
						Bayar dengan metode pembayaran pilihan Anda.
					</p>
				</div>
				<div class="my-4 h-16">
					<div class="flex items-center mb-2 text-sm text-gray-600">
						<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
								clip-rule="evenodd"></path>
						</svg>
						<span>Transfer Bank</span>
					</div>
					<div class="flex items-center text-sm text-gray-600">
						<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
								clip-rule="evenodd"></path>
						</svg>
						<span>E-wallet & QRIS</span>
					</div>
				</div>
				{{-- <div class="space-y-3 mt-4">
					<button
						class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center justify-center">
						<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
							<path fill-rule="evenodd"
								d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100-4 2 2 0 000 4z"
								clip-rule="evenodd"></path>
						</svg>
						Opsi Pembayaran
					</button>
					<a href="#pembayaran" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Pelajari
						lebih lanjut</a>
				</div> --}}
			</div>

			<!-- Step 4 -->
			<div
				class="bg-white rounded-lg shadow-lg p-6 text-center relative hover:shadow-xl hover:transform hover:scale-105 transition-all duration-300 z-10">
				<div
					class="absolute -top-3 left-0 right-0 mx-auto w-24 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
					Langkah Keempat
				</div>
				<div
					class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center text-xl font-bold mx-auto mb-4 border-4 border-green-100">
					4
				</div>
				<div class="h-14">
					<h3 class="text-lg font-bold mb-1">Mulai Kursus</h3>
					<p class="text-gray-500 text-xs">
						Ikuti jadwal dan mulai kursus mengemudi Anda.
					</p>
				</div>
				<div class="my-4 h-16">
					<div class="flex items-center mb-2 text-sm text-gray-600">
						<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
								clip-rule="evenodd"></path>
						</svg>
						<span>Materi siap pakai</span>
					</div>
					<div class="flex items-center text-sm text-gray-600">
						<svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
								clip-rule="evenodd"></path>
						</svg>
						<span>Instruktur berpengalaman</span>
					</div>
				</div>
				{{-- <div class="space-y-3 mt-4">
					<button
						class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center justify-center">
						<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
								clip-rule="evenodd"></path>
						</svg>
						Persiapan Kursus
					</button>
					<a href="#persiapan" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Tips
						persiapan</a>
				</div> --}}
			</div>

		</div>

		<!-- Mobile view helper text -->
		<div class="md:hidden text-center mt-6 text-sm text-gray-500">
			<p>Swipe untuk melihat semua langkah pendaftaran</p>
		</div>
		</div>
	</section>

	<!-- Testimonial Section -->
	<section class="py-16 bg-white">
		<div class="w-full max-w-7xl mx-auto px-6">
			<div class="text-center mb-12">
				<span class="text-blue-600 text-sm font-medium">Apa Kata Siswa Kami</span>
				<h2 class="text-3xl md:text-4xl font-bold mt-2">Testimoni</h2>
				<p class="mt-4 max-w-2xl mx-auto text-gray-600">
					Dengarkan pengalaman langsung dari siswa yang telah menyelesaikan kursus mengemudi bersama kami.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<!-- Testimonial 1 -->
				<div class="bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
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
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
					</div>
					<p class="text-gray-700 italic">
						"Saya sangat puas dengan kursus mengemudi di Natuna. Instruktur sangat sabar dan profesional.
						Dalam waktu singkat saya sudah bisa mengemudi dengan percaya diri dan lulus ujian SIM pertama
						kali!"
					</p>
				</div>

				<!-- Testimonial 2 -->
				<div class="bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
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
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
					</div>
					<p class="text-gray-700 italic">
						"Fasilitas dan kendaraan untuk latihan sangat bagus dan terawat. Instrukturnya juga sangat
						kompeten dalam mengajarkan teknik berkendara yang aman. Rekomendasi banget!"
					</p>
				</div>

				<!-- Testimonial 3 -->
				<div class="bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
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
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
						<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
							</path>
						</svg>
					</div>
					<p class="text-gray-700 italic">
						"Saya mengambil paket profesional dan sangat terkesan dengan metode pengajaran yang
						komprehensif. Sekarang saya lebih percaya diri mengemudi di segala kondisi jalan dan cuaca."
					</p>
				</div>
			</div>

			<div class="mt-12 text-center">
				<a href="/testimonials" class="inline-flex items-center text-blue-600 hover:text-blue-800">
					Lihat lebih banyak testimoni
					<svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20"
						xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd"
							d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
							clip-rule="evenodd"></path>
					</svg>
				</a>
			</div>
		</div>
	</section>
	<!-- Contact & Maps Section -->
	<section id="kontak" class="py-16 bg-gray-800 text-white">
		<div class="w-full max-w-7xl mx-auto px-6">
			<div class="text-center mb-12">
				<span class="text-blue-400 text-sm font-medium">Hubungi Kami</span>
				<h2 class="text-3xl md:text-4xl font-bold mt-2">Kontak & Lokasi</h2>
				<p class="mt-4 max-w-2xl mx-auto text-gray-300">
					Kami siap membantu Anda dengan segala pertanyaan. Kunjungi kami atau hubungi melalui kontak berikut.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-10">
				<!-- Map -->
				<div class="h-96 bg-gray-700 rounded-lg overflow-hidden shadow-lg">
					<iframe
						src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.422126933587!2d106.82194659999999!3d-6.339334199999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ed4b3416c7d5%3A0x421eb6bab577b39d!2sJl.%20Moch%20Kahfi%20II%20No.39%2C%20RT.9%2FRW.8%2C%20Srengseng%20Sawah%2C%20Kec.%20Jagakarsa%2C%20Kota%20Jakarta%20Selatan%2C%20Daerah%20Khusus%20Ibukota%20Jakarta%2012630!5e0!3m2!1sid!2sid!4v1749235436860!5m2!1sid!2sid"
						width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
						referrerpolicy="no-referrer-when-downgrade">
					</iframe>
				</div>

				<!-- Contact Information -->
				<div class="flex flex-col space-y-6">
					<div>
						<h3 class="text-xl font-semibold mb-3 flex items-center">
							<svg class="w-6 h-6 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 20 20"
								xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd"
									d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
									clip-rule="evenodd"></path>
							</svg>
							Alamat Kami
						</h3>
						<p class="text-gray-300 pl-8">Jl. Moch Kahfi II No.39, RT.9/RW.8, Srengseng Sawah, Kec.
							Jagakarsa, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta
							12630</p>
					</div>

					<div>
						<h3 class="text-xl font-semibold mb-3 flex items-center">
							<svg class="w-6 h-6 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 20 20"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z">
								</path>
								<path fill-rule="evenodd"
									d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100-4 2 2 0 000 4z"
									clip-rule="evenodd"></path>
							</svg>
							<span>info@natunadriving.com</span>
							</li>
							<li class="flex items-center">
								<svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor"
									viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
									</path>
								</svg>
								<span>+62 812 1392 7692</span>
							</li>
					</div>

					<div>
						<h3 class="text-xl font-semibold mb-3 flex items-center">
							<svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor"
								viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
									d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
									clip-rule="evenodd"></path>
							</svg>
							Jam Operasional
						</h3>
						<p class="text-gray-300 pl-8">
							Senin - Jumat: 08:00 - 18:00<br>
							Sabtu: Tutup/Libur <br>
							Minggu: 08:00 - 15:00
						</p>
					</div>

					<div class="mt-6">
						<h3 class="text-xl font-semibold mb-3">Ikuti Kami</h3>
						<div class="flex space-x-4">
							<a href="#" class="bg-blue-600 p-2 rounded-full hover:bg-blue-500 transition-colors">
								<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
								</svg>
							</a>
							<a href="#" class="bg-pink-600 p-2 rounded-full hover:bg-pink-500 transition-colors">
								<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.63c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" />
								</svg>
							</a>
							<a href="#" class="bg-blue-400 p-2 rounded-full hover:bg-blue-300 transition-colors">
								<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
								</svg>
							</a>
							<a href="#" class="bg-red-600 p-2 rounded-full hover:bg-red-500 transition-colors">
								<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
								</svg>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</section>

	<!-- Footer -->
	<footer class="bg-gray-900 text-gray-400 py-8">

		<!-- Footer -->
		<footer class="bg-gray-900 text-gray-400 py-8">
			<div class="w-full max-w-7xl mx-auto px-6">
				<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
					<div>
						<img src="{{ asset('assets/img/logo.png') }}" alt="Logo Natuna" class="h-10 mb-4" />
						<p class="text-sm">
							Natuna Driving Academy menyediakan pelatihan berkendara profesional dengan standar keamanan
							tinggi dan instruktur berpengalaman.
						</p>
					</div>
					<div>
						<h4 class="text-white text-lg font-medium mb-4">Program Kursus</h4>
						<ul class="space-y-2 text-sm">
							<li><a href="#" class="hover:text-blue-400 transition-colors">Kursus SIM A</a></li>
							<li><a href="#" class="hover:text-blue-400 transition-colors">Kursus SIM C</a></li>

						</ul>
					</div>
					<div>
						<h4 class="text-white text-lg font-medium mb-4">Bantuan</h4>
						<ul class="space-y-2 text-sm">
							<li><a href="#" class="hover:text-blue-400 transition-colors">FAQ</a></li>
							<li><a href="#" class="hover:text-blue-400 transition-colors">Syarat & Ketentuan</a></li>
							<li><a href="#" class="hover:text-blue-400 transition-colors">Kebijakan Privasi</a></li>
							<li><a href="#" class="hover:text-blue-400 transition-colors">Bantuan</a></li>
						</ul>
					</div>
					<div>
						<h4 class="text-white text-lg font-medium mb-4">Hubungi Kami</h4>
						<ul class="space-y-3 text-sm">
							<li class="flex items-start">
								<svg class="w-5 h-5 mr-2 mt-0.5 text-blue-400" fill="none" stroke="currentColor"
									viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
									</path>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
								</svg>
								<span>Jl. Moch Kahfi II No.39, RT.9/RW.8, Srengseng Sawah, Kec. Jagakarsa, Kota Jakarta
									Selatan, Daerah Khusus Ibukota Jakarta
									12630</span>
							</li>
							<li class="flex items-center">
								<svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor"
									viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
									</path>
								</svg>
								<span>info@natunadriving.com</span>
							</li>
							<li class="flex items-center">
								<svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor"
									viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V19a2 2 0 01-1 1h-1C9.716 21 3 14.284 3 6V5z">
									</path>
								</svg>
								<a href="https://wa.me/6281213927692" target="_blank">
									<span>+62 812 1392 7692</span>
								</a>
							</li>
					</div>

				</div>
			</div>
		</footer>