@php($hideNavbar = true)

@extends('app')

@section('title', 'Status Booking & Pembayaran')

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

@push('scripts')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
@endpush

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12 px-4">
        <div class="max-w-4xl mx-auto animate-fade-in-up">

            <!-- Header -->
            <div class="text-center mb-8">
            {{-- ICON STATUS --}}
            <div
                @if($paymentState === 'success')
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 animate-pulse-slow bg-green-100"
                @elseif($paymentState === 'pending')
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 animate-pulse-slow bg-yellow-100"
                @elseif($paymentState === 'failed')
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 animate-pulse-slow bg-red-100"
                @else
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 animate-pulse-slow bg-blue-100"
                @endif
            >
                <span class="text-3xl">
                    @if($paymentState === 'success')
                        🎉
                    @elseif($paymentState === 'pending')
                        ⏳
                    @elseif($paymentState === 'failed')
                        ❌
                    @else
                        📄
                    @endif
                </span>
            </div>

            {{-- TITLE + TEXT --}}
            @if($paymentState === 'success')
                <h1 id="status-title" class="text-3xl font-bold text-gray-800 mb-2">Booking & Pembayaran Berhasil</h1>
                <p id="status-subtext" class="text-gray-600">
                    Pembayaran kamu sudah kami terima. Jadwal foto kamu sudah <span class="font-semibold">fix</span>.
                </p>

            @elseif($paymentState === 'pending')
                <h1 id="status-title" class="text-3xl font-bold text-gray-800 mb-2">Booking Berhasil Dibuat</h1>
                <p id="status-subtext" class="text-gray-600">
                    Data booking sudah tersimpan. Selesaikan pembayaran dulu supaya slot kamu tidak dibatalkan.
                </p>

            @elseif($paymentState === 'failed')
                <h1 id="status-title" class="text-3xl font-bold text-gray-800 mb-2">Booking Dibatalkan</h1>
                <p id="status-subtext" class="text-gray-600">
                    Pembayaran tidak berhasil atau sudah kedaluwarsa. Silakan buat booking baru jika masih ingin lanjut.
                </p>

            @else
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Status Booking & Pembayaran</h1>
                <p class="text-gray-600">Silakan cek detail booking di bawah ini.</p>
            @endif
        </div>

            <!-- Main Booking Card -->
            <div class="bg-white rounded-2xl card-shadow overflow-hidden mb-6">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h2 class="text-xl font-bold text-white">Detail Booking</h2>
        @if($bookingData['status'] === 'Pending Payment')
            <span id="status-badge" class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 border border-yellow-300">
                {{ $bookingData['status'] }}
            </span>
        @elseif(in_array($bookingData['status'], ['Paid - DP', 'Paid - Full']))
            <span id="status-badge" class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                {{ $bookingData['status'] }}
            </span>
        @elseif($bookingData['status'] === 'Cancelled')
            <span id="status-badge" class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-300">
                {{ $bookingData['status'] }}
            </span>
        @else
            <span id="status-badge" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 border border-gray-300">
                {{ $bookingData['status'] }}
            </span>
        @endif
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

                    <div class="mt-6 text-center space-y-3">
                        <div
                            class="inline-flex items-center space-x-2 text-sm text-gray-500 bg-gray-100 rounded-full px-4 py-2">
                            <i class="fas fa-clock"></i>
                            <span>Booking dibuat pada {{ $bookingData['created_at'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                @if($paymentState === 'pending')
                    <a href="{{ route('booking.pay', $booking) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-credit-card mr-2"></i>
                        Lanjutkan Pembayaran
                    </a>
                @endif
                <a href="{{ url('/') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-8 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
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
                        <button onclick="refreshBookingStatus()" class="flex items-center text-green-600 hover:text-green-800 ml-4">
                            <i class="fas fa-sync-alt mr-1"></i>
                            Refresh Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-update status every 10 seconds (jika status pending)
        let statusCheckInterval = null;

        const bookingKey = @json($booking->getRouteKey());
        const paymentStateFromServer = @json($paymentState);
        const syncUrl = @json(url("/booking")) + `/${bookingKey}/sync-payment`;
        const statusUrl = @json(url("/booking")) + `/${bookingKey}/status`;

        function startStatusPolling() {
        if (paymentStateFromServer !== 'pending') return;

        statusCheckInterval = setInterval(async () => {
            try {
                // OPTIONAL: sync dulu kalau kamu bikin endpoint sync-payment
                // (kalau belum ada, comment aja 2 baris ini)
                await fetch(syncUrl, { method: 'GET' });

                const response = await fetch(statusUrl, { method: 'GET' });
                if (!response.ok) return;

                const data = await response.json();

                if (data && data.success) {
                    // kalau status sudah bukan pending -> reload biar blade render ulang
                    if (data.payment_state && data.payment_state !== 'pending') {
                        clearInterval(statusCheckInterval);
                        if (countdownInterval) clearInterval(countdownInterval);
                        window.location.reload();
                    }
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        }, 3000); // 3 detik biar responsif
    }

    async function refreshBookingStatus() {
        try {
            // OPTIONAL: sync dulu kalau endpoint ada
            await fetch(syncUrl, { method: 'GET' });

            const response = await fetch(statusUrl, { method: 'GET' });
            if (!response.ok) throw new Error('Gagal memperbarui status');

            const data = await response.json();

            if (!data || !data.success) {
                alert('Status tidak berubah. Silakan coba lagi nanti.');
                return;
            }

            // kalau masih pending, biarin polling jalan
            if (data.payment_state === 'pending') return;

            // kalau kamu mau update UI tanpa reload:
            if (typeof applyStatusUpdateFromApi === 'function') {
                applyStatusUpdateFromApi(data);
            } else {
                // fallback: reload
                window.location.reload();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi error saat memperbarui status: ' + error.message);
        }
    }

    // ====== INIT ======
    document.addEventListener('DOMContentLoaded', () => {
        startStatusPolling();
        startCountdownTimer();
    });

    window.addEventListener('beforeunload', () => {
        if (statusCheckInterval) clearInterval(statusCheckInterval);
        if (countdownInterval) clearInterval(countdownInterval);
    });

        function continuePayment() {
            const snapToken = '{{ $bookingData['snap_token'] ?? '' }}';

            if (!snapToken) {
                alert('Token pembayaran tidak tersedia. Silakan refresh halaman.');
                return;
            }

            if (typeof window.snap === 'undefined') {
                alert('Midtrans Snap belum dimuat. Silakan refresh halaman dan coba lagi.');
                return;
            }

            // Buka Midtrans Snap payment popup
            window.snap.pay(snapToken, {
                onSuccess: function (result) {
                    console.log('Pembayaran berhasil:', result);
                    // Tunggu beberapa detik untuk webhook memproses
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                },
                onPending: function (result) {
                    console.log('Pembayaran pending:', result);
                    alert('Pembayaran sedang diproses. Silakan tunggu.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                },
                onError: function (result) {
                    console.error('Pembayaran gagal:', result);
                    alert('Pembayaran gagal: ' + (result.status_message || 'Silakan coba lagi'));
                },
                onClose: function () {
                    console.log('Pembayaran popup ditutup');
                    alert('Anda menutup pembayaran. Klik tombol "Lanjutkan Pembayaran" untuk mencoba lagi.');
                }
            });
        }
    </script>
@endsection
