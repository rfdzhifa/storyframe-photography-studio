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

class BookingController extends Controller
{
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

        return view('pages.booking', compact('services','packages','selectedService','selectedPackage','minDate','maxDate'));
    }

    /**
     * Mengambil slot waktu yang tersedia berdasarkan tanggal dan service yang dipilih.
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'package' => 'required|exists:packages,id',
        ]);

        $date = Carbon::parse($request->input('date'));
        $dayName = $date->format('l'); // "Monday", "Tuesday", etc.
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

        // Generate slot dengan tanggal spesifik
        $allSlots = $weeklySchedule->generateSlots($date->toDateString(), $duration);

        $serviceId = ServicePackage::where('package_id', $package->id)
            ->where('is_active', true)
            ->value('service_id');

        $bookedSlots = Booking::whereDate('booking_date', $date->toDateString())
            ->where('service_id', $serviceId)
            ->whereHas('bookingStatus', function ($q) {
                // Hanya cek booking yang tidak Cancelled (id=4) atau Rejected
                $q->whereNotIn('id', [4]); // 4 = Cancelled
            })
            ->get();

        // Ambil array waktu yang sudah dibooking
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

    /**
     * Halaman checkout - form + ringkasan booking
     */
    public function checkout(Request $request)
    {
        $serviceId = $request->input('service');
        $packageId = $request->input('package');

        if (!$serviceId || !$packageId) {
            abort(404, 'Service atau Package tidak dikirim dari halaman sebelumnya.');
        }

        $service = Service::findOrFail($serviceId);
        $package = Package::findOrFail($packageId);

        $price = $request->input('price');

        if ($price === null) {
            $price = DB::table('service_packages')
                ->where('service_id', $service->id)
                ->where('package_id', $package->id)
                ->value('price') ?? $package->price;
        }

        $booking = [
            'service_id'     => $service->id,
            'service_name'   => $service->name,
            'package_id'     => $package->id,
            'package_name'   => $package->name,
            'price'          => (int) $price,
            'booking_date'   => $request->input('date'),
            'preferred_time' => $request->input('time'),
        ];

        return view('pages.checkout', compact('booking'));
    }

    /**
     * Menyimpan booking SEMENTARA dengan status PENDING
     * Belum bayar, jadi belum kirim email
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'email'           => 'required|email',
            'phone_number'    => 'required|string|max:20',
            'payment'         => 'required|in:dp,full',
            'notes'           => 'nullable|string',
            'service'         => 'required|exists:services,id',
            'package'         => 'required|exists:packages,id',
            'price'           => 'required|numeric',
            'booking_date'    => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(30)->toDateString(),
            'preferred_time'  => 'required|date_format:H:i',
        ]);

        DB::beginTransaction();

        try {
            $service = Service::findOrFail($validated['service']);
            $package = Package::findOrFail($validated['package']);
            $duration = (int) $package->duration_minutes;

            $bookingDate = Carbon::parse($validated['booking_date']);
            $startTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $bookingDate->toDateString() . ' ' . $validated['preferred_time']
            );
            $endTime = $startTime->copy()->addMinutes($duration);

            // CEK TABRAKAN SLOT
            $slotTaken = Booking::where('service_id', $service->id)
                ->whereDate('booking_date', $bookingDate->toDateString())
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime->format('H:i:s'))
                          ->where('end_time', '>', $startTime->format('H:i:s'));
                })
                ->whereHas('bookingStatus', function ($q) {
                    // Status: 1=Pending, 2=Confirmed, 3=Completed, 5=Rescheduled
                    // Jangan cek yang sudah Cancelled (4)
                    $q->whereNotIn('id', [4]);
                })
                ->exists();

            if ($slotTaken) {
                throw new \Exception('Slot sudah diambil. Silakan pilih waktu lain.');
            }

            // CEK JADWAL STUDIO
            $dayName = $bookingDate->format('l');
            $studioSchedule = WeeklySchedule::where('day_of_week', $dayName)
                ->where('is_available', true)
                ->first();

            if (!$studioSchedule) {
                throw new \Exception('Studio tutup di hari yang dipilih.');
            }

            // AMBIL HARGA DARI PIVOT
            $pivotPrice = DB::table('service_packages')
                ->where('service_id', $service->id)
                ->where('package_id', $package->id)
                ->value('price');

            if ($pivotPrice === null) {
                throw new \Exception('Harga kombinasi service & package tidak ditemukan.');
            }

            $totalPrice    = (float) $pivotPrice;
            $paymentOption = $validated['payment'];
            $dpAmount      = $paymentOption === 'dp' ? $totalPrice * 0.5 : null;

            // SIMPAN BOOKING DENGAN STATUS: 1 = Pending
            $booking = Booking::create([
                'customer_name'       => $validated['full_name'],
                'customer_email'      => $validated['email'],
                'customer_phone'      => $validated['phone_number'],
                'service_id'          => $service->id,
                'package_id'          => $package->id,
                'booking_status_id'   => 1, // Pending
                'booking_date'        => $bookingDate->toDateString(),
                'start_time'          => $startTime->format('H:i:s'),
                'end_time'            => $endTime->format('H:i:s'),
                'total_price'         => $totalPrice,
                'notes'               => $validated['notes'] ?? null,
                'payment_option'      => $paymentOption,
                'down_payment_amount' => $dpAmount,
                'payment_status'      => 'pending',
            ]);

            // COMMIT: Booking tersimpan dengan status PENDING (belum bayar)
            DB::commit();

            // RESPONSE: Redirect ke Payment
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking berhasil dibuat. Lanjut ke pembayaran...',
                    'data' => [
                        'booking_id'   => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'redirect_url' => route('booking.payment.checkout', $booking->booking_code)
                    ]
                ], 200);
            }

            return redirect()->route('booking.payment.checkout', $booking->booking_code);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Booking gagal: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi error saat booking: ' . $e->getMessage(),
                    'errors'  => ['error' => $e->getMessage()],
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Terjadi error saat booking: ' . $e->getMessage()])
                ->withInput();
        }
    }



    /**
     * Halaman sukses booking (setelah payment berhasil)
     */
    public function success(Booking $booking)
    {
        // Load relasi yang dibutuhkan
        $booking->load(['service', 'package', 'bookingStatus']);

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
            'status_color' => $booking->bookingStatus->color_indicator,
            'notes' => $booking->notes,
            'created_at' => $booking->created_at->format('d F Y, H:i')
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

    /**
     * Halaman katalog
     */
    public function catalog()
    {
        $services = Service::where('is_active', true)
            ->with('packages')
            ->orderBy('name')
            ->get();

        return view('pages.catalog', compact('services'));
    }

    /**
     * Halaman detail service
     */
    public function detail(Service $service)
    {
        $service->load(['packages' => function ($q) {
            $q->withPivot(['price'])->orderBy('packages.name');
        }]);

        return view('pages.detail', compact('service'));
    }
}