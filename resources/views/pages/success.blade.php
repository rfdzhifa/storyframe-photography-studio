@php($hideNavbar = true)

@extends('app')

@section('title', 'Booking Berhasil')

@push('styles')
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }

        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12 px-4">
        <div class="max-w-4xl mx-auto animate-fade-in-up">
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4 animate-pulse-slow">
                    <i class="fas fa-check text-3xl text-green-500">🎉</i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Booking Berhasil!</h1>
                <p class="text-gray-600">Terima kasih telah mempercayai layanan kami</p>
            </div>

            <!-- Main Booking Card -->
            <div class="bg-white rounded-2xl card-shadow overflow-hidden mb-6">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 class="text-xl font-bold text-white">Detail Booking</h2>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm text-white font-medium">
                            {{ $bookingData['status'] }}
                        </span>
                    </div>
                </div>

                <!-- Booking Code Highlight -->
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Kode Booking Anda</p>
                        <div class="bg-white rounded-lg px-4 py-3 inline-block shadow-sm">
                            <p class="text-2xl font-bold text-gray-800 tracking-wider">{{ $bookingData['booking_code'] }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Simpan kode ini untuk referensi booking Anda
                        </p>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Customer Information -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <i class="fas fa-user-circle text-blue-500 text-xl"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Informasi Customer</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-user text-blue-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Nama Lengkap</p>
                                        <p class="font-medium text-gray-800">{{ $bookingData['customer_name'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-envelope text-blue-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Email</p>
                                        <p class="font-medium text-gray-800 break-all">{{ $bookingData['customer_email'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-phone text-blue-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">No. Telepon</p>
                                        <p class="font-medium text-gray-800">{{ $bookingData['customer_phone'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <i class="fas fa-calendar-check text-purple-500 text-xl"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Detail Booking</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-camera text-purple-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Layanan</p>
                                        <p class="font-medium text-gray-800">{{ $bookingData['service_name'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-box text-purple-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Paket</p>
                                        <p class="font-medium text-gray-800">{{ $bookingData['package_name'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-calendar text-purple-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Tanggal</p>
                                        <p class="font-medium text-gray-800">{{ $bookingData['booking_date'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-clock text-purple-500 w-5 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Waktu</p>
                                        <p class="font-medium text-gray-800">{{ $bookingData['start_time'] }} -
                                            {{ $bookingData['end_time'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mt-8 bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <i class="fas fa-credit-card text-amber-500 text-xl"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Informasi Pembayaran</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-600 mb-1">Total Harga</p>
                                <p class="text-2xl font-bold text-gray-800">Rp {{ $bookingData['total_price'] }}</p>
                            </div>

                            <div class="bg-white rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-600 mb-1">Metode Pembayaran</p>
                                <p class="font-semibold text-gray-800">{{ $bookingData['payment_option'] }}</p>
                            </div>

                            @if($bookingData['down_payment_amount'])
                                <div class="bg-white rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600 mb-1">Down Payment</p>
                                    <p class="text-xl font-bold text-orange-600">Rp {{ $bookingData['down_payment_amount'] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($bookingData['notes'])
                        <div class="mt-6 bg-blue-50 rounded-xl p-6">
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="fas fa-sticky-note text-blue-500"></i>
                                <h3 class="font-semibold text-gray-800">Catatan Khusus</h3>
                            </div>
                            <div class="bg-white rounded-lg p-4">
                                <p class="text-gray-700 italic">"{{ $bookingData['notes'] }}"</p>
                            </div>
                        </div>
                    @endif

                    <!-- Timestamp -->
                    <div class="mt-6 text-center">
                        <div
                            class="inline-flex items-center space-x-2 text-sm text-gray-500 bg-gray-100 rounded-full px-4 py-2">
                            <i class="fas fa-clock"></i>
                            <span>Booking dibuat pada {{ $bookingData['created_at'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                <a href="{{ url('/') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                    <i class="fas fa-home mr-2"></i>
                    Kembali ke Home
                </a>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-white rounded-xl p-6 border border-gray-200">
                <div class="text-center">
                    <div class="flex items-center justify-center space-x-2 mb-3">
                        <i class="fas fa-question-circle text-gray-500"></i>
                        <h3 class="font-semibold text-gray-800">Butuh Bantuan?</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Jika ada pertanyaan tentang booking Anda, jangan ragu untuk menghubungi kami
                    </p>
                    <div
                        class="flex flex-col sm:flex-row items-center justify-center space-y-2 sm:space-y-0 sm:space-x-4 text-sm">
                        <a href="tel:+628123456789" class="flex items-center text-blue-600 hover:text-blue-800">
                            <i class="fas fa-phone mr-1"></i>
                            +62 812-3456-789
                        </a>
                        <a href="mailto:info@studio.com" class="flex items-center text-blue-600 hover:text-blue-800">
                            <i class="fas fa-envelope mr-1"></i>
                            info@studio.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection