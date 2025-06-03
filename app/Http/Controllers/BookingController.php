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
    public function index()
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        // Batasan tanggal: dari hari ini sampai 30 hari ke depan
        $minDate = Carbon::today()->toDateString();
        $maxDate = Carbon::today()->addDays(30)->toDateString();

        return view('pages.booking', compact('services', 'packages', 'minDate', 'maxDate'));
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
             foreach ($bookedTimes as $booked) {
                 if (
                     ($slot['start'] >= $booked['start'] && $slot['start'] < $booked['end']) ||
                     ($slot['end'] > $booked['start'] && $slot['end'] <= $booked['end']) ||
                     ($slot['start'] <= $booked['start'] && $slot['end'] >= $booked['end']) // full overlap
                 ) {
                     return false;
                 }
             }
             return true;
         })->values();
     
         return response()->json($availableSlots);
     }


    /**
     * Menyimpan data booking baru.
     */
    public function store(Request $request)
{
    $request->validate([
        'full_name' => 'required|string|max:100',
        'email' => 'required|email:rfc,dns|max:100',
        'phone_number' => 'required|string|max:15',
        'service' => 'required|exists:services,id',
        'package' => 'required|exists:packages,id',
        'payment' => 'required|in:dp,full',
        'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(30)->toDateString(),
        'preferred_time' => 'required|date_format:H:i',
        'notes' => 'nullable|string|max:500',
    ]);
    

    DB::beginTransaction();

    try {
        $service = Service::findOrFail($request->service);
        $package = Package::findOrFail($request->package);
        $duration = (int) $package->duration_minutes;

        $bookingDate = Carbon::parse($request->booking_date);
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $request->booking_date . ' ' . $request->preferred_time);
        $endTime = $startTime->copy()->addMinutes($duration);

        // Check booking collision
        $slotTaken = Booking::where('service_id', $service->id)
            ->where('booking_date', $bookingDate->toDateString())
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

        $dayOfWeek = $bookingDate->dayOfWeek;

        $studioSchedule = WeeklySchedule::where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$studioSchedule) {
            throw new \Exception('Studio tutup di hari yang dipilih.');
        }

        $pendingStatus = BookingStatus::where('name', 'Pending')->first();
        if (!$pendingStatus) {
            throw new \Exception('BookingStatus "Pending" tidak ditemukan!');
        }
        
        $pivotPrice = DB::table('service_packages')
    ->where('service_id', $service->id)
    ->where('package_id', $package->id)
    ->value('price');

    if ($pivotPrice === null) {
        throw new \Exception('Harga kombinasi service & package tidak ditemukan.');
    }


        $totalPrice = (float) $pivotPrice;
        $paymentOption = $request->payment;
        $dpAmount = $paymentOption === 'dp' ? $totalPrice * 0.5 : null;

        $booking = Booking::create([
            'booking_code' => 'BOOK-' . strtoupper(Str::random(8)),
            'customer_name' => $request->full_name,
            'customer_email' => $request->email,
            'customer_phone' => $request->phone_number,
            'service_id' => $service->id,
            'package_id' => $package->id,
            'booking_status_id' => $pendingStatus->id,
            'booking_date' => $bookingDate->toDateString(),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'total_price' => $totalPrice,
            'notes' => $request->notes,
            'payment_option' => $paymentOption,
            'down_payment_amount' => $dpAmount,
            'payment_status' => 'pending',
        ]);

        DB::commit();

        Mail::to($booking->customer_email)->send(new BookingSuccessMail($booking));
        
        $redirectUrl = route('booking.success', ['booking' => $booking]);


        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking sukses! ID kamu: ' . $booking->booking_code,
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'redirect_url' => $redirectUrl
                ]
            ], 200);
        }

    } catch (\Exception $e) {

        // Super verbose logging biar jelas banget errornya apa
        Log::error("Booking gagal: " . $e->getMessage());
        Log::error($e->getTraceAsString());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat booking: ' . $e->getMessage(),
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }

        return back()->withErrors(['error' => 'Terjadi error saat booking: ' . $e->getMessage()])->withInput();
    }
}



    /**
     * Halaman sukses booking
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
     
         $packages = DB::table('service_packages')
             ->join('packages', 'service_packages.package_id', '=', 'packages.id')
             ->where('service_packages.service_id', $request->service_id)
             ->where('service_packages.is_active', true)
             ->orderBy('packages.name')
             ->select('packages.id', 'packages.name', 'service_packages.price', 'packages.description')
             ->get();
     
         return response()->json($packages);
     }     
}