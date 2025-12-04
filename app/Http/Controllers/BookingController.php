<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Service;
use App\Models\WeeklySchedule;
use App\Models\BookingStatus;
use App\Models\ServicePackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingSuccessMail;
use Midtrans\Config;
use Midtrans\Snap;

class BookingController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }
    /**
     * Menampilkan halaman booking dengan data awal.
     */
    public function index(Request $request)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        $selectedService = $request->query('service');
        $selectedPackage = $request->query('package');

        // Batasan tanggal: dari hari ini sampai 30 hari ke depan
        $minDate = Carbon::today()->toDateString();
        $maxDate = Carbon::today()->addDays(30)->toDateString();

        return view('pages.booking', compact('services', 'packages', 'selectedService', 'selectedPackage', 'minDate', 'maxDate'));
    }

    /**
     * Mengambil slot waktu yang tersedia berdasarkan tanggal dan service yang dipilih.
     * API endpoint ini akan dipanggil oleh JavaScript.
     */

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'package' => 'required|exists:packages,id',
        ]);

        $date = Carbon::parse($request->input('date'));
        $dayName = $date->format('l'); // Contoh: "Monday"
        $packageId = $request->input('package');

        // Ambil durasi dari package
        $package = Package::findOrFail($packageId);
        $duration = $package->duration_minutes;

        // Ambil jadwal berdasarkan nama hari
        $weeklySchedule = WeeklySchedule::where('day_of_week', $dayName)
            ->where('is_available', true)
            ->first();

        if (!$weeklySchedule) {
            return response()->json(['error' => 'Studio not available on this day.'], 404);
        }

        // Generate slot dengan tanggal spesifik dari user + jam dari jadwal mingguan
        $allSlots = $weeklySchedule->generateSlots($date->toDateString(), $duration);

        $serviceId = ServicePackage::where('package_id', $package->id)
            ->where('is_active', true)
            ->value('service_id');

        $bookedSlots = Booking::whereDate('booking_date', $date->toDateString())
            ->where('service_id', $serviceId)
            ->get();

        // Ambil array dari waktu mulai booking
        $bookedTimes = [];
        foreach ($bookedSlots as $booking) {
            $start = Carbon::parse($booking->start_time)->format('H:i');
            $end = Carbon::parse($booking->end_time)->format('H:i');

            $bookedTimes[] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        // Filter slot yang bentrok
        $availableSlots = collect($allSlots)->filter(function ($slot) use ($bookedTimes) {
            $slotStart = Carbon::createFromFormat('H:i', $slot['start']);
            $slotEnd = Carbon::createFromFormat('H:i', $slot['end']);

            foreach ($bookedTimes as $booked) {
                $bookedStart = Carbon::createFromFormat('H:i', $booked['start']);
                $bookedEnd = Carbon::createFromFormat('H:i', $booked['end']);

                if (
                    ($slotStart >= $bookedStart && $slotStart < $bookedEnd) ||
                    ($slotEnd > $bookedStart && $slotEnd <= $bookedEnd) ||
                    ($slotStart <= $bookedStart && $slotEnd >= $bookedEnd)
                ) {
                    return false;
                }
            }
            return true;
        })->values();

        return response()->json($availableSlots);
    }

    public function checkout(Request $request)
    {
        // ambil dari query: ?service= & ?package=
        $serviceId = $request->input('service');
        $packageId = $request->input('package');

        if (!$serviceId || !$packageId) {
            abort(404, 'Service atau Package tidak dikirim dari halaman sebelumnya.');
        }

        $service = Service::findOrFail($serviceId);
        $package = Package::findOrFail($packageId);

        // harga dari query, kalau kosong ambil dari pivot
        $price = $request->input('price');

        if ($price === null) {
            $price = DB::table('service_packages')
                ->where('service_id', $service->id)
                ->where('package_id', $package->id)
                ->value('price') ?? $package->price;
        }

        $booking = [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => (int) $price,
            'booking_date' => $request->input('date'),  // dari query ?date=
            'preferred_time' => $request->input('time'),  // dari query ?time=
        ];

        return view('pages.checkout', compact('booking'));
    }

    // proses ketika klik "Bayar Sekarang"
    public function store(Request $request)
    {
        // 1. VALIDASI INPUT DARI HALAMAN CHECKOUT
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string|max:20',
            'payment' => 'required|in:dp,full',   // dp / full
            'notes' => 'nullable|string',
            'service' => 'required|exists:services,id',
            'package' => 'required|exists:packages,id',
            'price' => 'required|numeric',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(30)->toDateString(),
            'preferred_time' => 'required|date_format:H:i',
        ]);

        DB::beginTransaction();

        try {
            // 2. AMBIL SERVICE & PACKAGE
            $service = Service::findOrFail($validated['service']);
            $package = Package::findOrFail($validated['package']);
            $duration = (int) $package->duration_minutes;

            // 3. HITUNG WAKTU MULAI & SELESAI
            $bookingDate = Carbon::parse($validated['booking_date']);
            $startTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $bookingDate->toDateString() . ' ' . $validated['preferred_time']
            );
            $endTime = $startTime->copy()->addMinutes($duration);

            // 4. CEK TABRAKAN SLOT DENGAN BOOKING LAIN
            $slotTaken = Booking::where('service_id', $service->id)
                ->whereDate('booking_date', $bookingDate->toDateString())
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime->format('H:i:s'))
                        ->where('end_time', '>', $startTime->format('H:i:s'));
                })
                ->whereHas('bookingStatus', function ($q) {
                    $q->whereNotIn('name', ['Cancelled', 'Rejected']);
                })
                ->exists();

            if ($slotTaken) {
                throw new \Exception('Slot sudah diambil. Silakan pilih waktu lain.');
            }

            // 5. CEK STUDIO BUKA/TUTUP BERDASARKAN JADWAL MINGGUAN
            // pake nama hari biar konsisten sama getAvailableSlots()
            $dayName = $bookingDate->format('l'); // "Monday", "Tuesday", ...
            $studioSchedule = WeeklySchedule::where('day_of_week', $dayName)
                ->where('is_available', true)
                ->first();

            if (!$studioSchedule) {
                throw new \Exception('Studio tutup di hari yang dipilih.');
            }

            // 6. AMBIL STATUS "Pending"
            $pendingStatus = BookingStatus::where('name', 'Pending Payment')->first();
            if (!$pendingStatus) {
                throw new \Exception('BookingStatus "Pending" tidak ditemukan!');
            }

            // 7. AMBIL HARGA DARI PIVOT (LEBIH AMAN DARIPADA PERCAYA INPUT USER)
            $pivotPrice = DB::table('service_packages')
                ->where('service_id', $service->id)
                ->where('package_id', $package->id)
                ->value('price');

            if ($pivotPrice === null) {
                throw new \Exception('Harga kombinasi service & package tidak ditemukan.');
            }

            $totalPrice = (float) $pivotPrice;
            $paymentOption = $validated['payment'];
            $dpAmount = $paymentOption === 'dp' ? $totalPrice * 0.5 : null;

            // 8. SIMPAN BOOKING KE DATABASE
            $booking = Booking::create([
                // kalau booking_code digenerate otomatis di model, bagian ini tidak perlu
                // 'booking_code' => 'BOOK-' . strtoupper(Str::random(8)),
                'customer_name' => $validated['full_name'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone_number'],
                'service_id' => $service->id,
                'package_id' => $package->id,
                'booking_status_id' => $pendingStatus->id,
                'booking_date' => $bookingDate->toDateString(),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'total_price' => $totalPrice,
                'notes' => $validated['notes'] ?? null,
                'payment_option' => $paymentOption,
                'down_payment_amount' => $dpAmount,
                'payment_status' => 'pending',
            ]);

            // 9. KIRIM EMAIL KONFIRMASI (OPSIONAL, SUDAH ADA DI KODE LAMA)
            Mail::to($booking->customer_email)->send(new BookingSuccessMail($booking));

            // 10. GENERATE SNAP TOKEN
            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => (int) $booking->total_price, // Pastikan integer
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $booking->customer_email,
                    'phone' => $booking->customer_phone,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            $redirectUrl = route('booking.success', ['booking' => $booking]);

            // 11. RESPONSE UNTUK AJAX / JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking sukses! Kode booking kamu: ' . $booking->booking_code,
                    'snap_token' => $snapToken,
                    'redirect_url' => $redirectUrl,
                    'data' => [
                        'booking_id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                    ],
                ], 201);
            }
            // 12. RESPONSE UNTUK FORM BIASA (NON AJAX)
            return redirect()->route('booking.success', $booking)
                ->with('success', 'Booking berhasil dibuat, silakan lanjut pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();

            // logging biar gampang debug
            Log::error("Booking gagal: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi error saat booking: ' . $e->getMessage(),
                    'errors' => ['error' => $e->getMessage()],
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Terjadi error saat booking: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Halaman sukses booking
     */
    public function success(Booking $booking)
    {
        // Load relasi yang dibutuhkan
        $booking->load(['service', 'package', 'bookingStatus']);

        // Generate Snap Token jika status masih pending
        $snapToken = null;
        if ($booking->bookingStatus->name == 'Pending Payment') {
            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => (int) $booking->total_price,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $booking->customer_email,
                    'phone' => $booking->customer_phone,
                ],
            ];
            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                Log::error('Midtrans Error: ' . $e->getMessage());
            }
        }

        // Format data untuk tampilan
        $bookingData = [
            'booking_code' => $booking->booking_code,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'customer_phone' => $booking->customer_phone,
            'service_name' => $booking->service->name,
            'package_name' => $booking->package->name,
            'booking_date' => Carbon::parse($booking->booking_date)->format('d F Y'),
            'start_time' => Carbon::parse($booking->start_time)->format('H:i'),
            'end_time' => Carbon::parse($booking->end_time)->format('H:i'),
            'total_price' => number_format($booking->total_price, 0, ',', '.'),
            'payment_option' => $booking->payment_option === 'dp' ? 'Down Payment (50%)' : 'Full Payment',
            'down_payment_amount' => $booking->down_payment_amount ? number_format($booking->down_payment_amount, 0, ',', '.') : null,
            'status' => $booking->bookingStatus->name,
            'notes' => $booking->notes,
            'created_at' => $booking->created_at->format('d F Y, H:i'),
            'snap_token' => $snapToken,
        ];

        return view('pages.success', compact('booking', 'bookingData'));
    }

    /**
     * API untuk mendapatkan packages berdasarkan service
     */

    public function getPackagesByService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        try {
            $packages = DB::table('service_packages')
                ->join('packages', 'service_packages.package_id', '=', 'packages.id')
                ->where('service_packages.service_id', $request->service_id)
                ->where('service_packages.is_active', true)
                ->orderBy('packages.name')
                ->select('packages.id', 'packages.name', 'service_packages.price', 'packages.description')
                ->get();

            return response()->json($packages);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat paket.'], 500);
        }
    }

    public function catalog()
    {
        $services = Service::where('is_active', true)
            ->with('packages') // penting: eager load + wherePivot di relasi
            ->orderBy('name')
            ->get();

        return view('pages.catalog', compact('services'));
    }
    public function detail(Service $service)
    {
        $service->load([
            'packages' => function ($q) {
                $q->withPivot(['price'])->orderBy('packages.name');
            }
        ]);

        return view('pages.detail', compact('service'));
    }

}
