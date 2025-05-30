<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Service;
use App\Models\StudioSchedule;
use App\Models\BookingStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            'date' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(30)->toDateString(),
            'service' => 'required|exists:services,id',
            'package' => 'required|exists:packages,id',
        ]);

        try {
            $selectedDate = Carbon::parse($request->date);
            $service = Service::findOrFail($request->service);
            $package = Package::findOrFail($request->package);
            $duration = (int) $package->duration_hours;

            if ($duration <= 0) {
                return response()->json(['error' => 'Invalid service duration.'], 400);
            }

            // Cari jadwal studio yang aktif
            // Bisa disesuaikan berdasarkan hari dalam seminggu jika perlu
            $studioSchedule = StudioSchedule::where('is_available', true)
                ->first(); // Atau tambahkan filter berdasarkan hari jika ada

            if (!$studioSchedule) {
                return response()->json(['error' => 'Studio schedule not available.'], 404);
            }

            $studioOpenTime = Carbon::parse($selectedDate->toDateString() . ' ' . $studioSchedule->open_time);
            $studioCloseTime = Carbon::parse($selectedDate->toDateString() . ' ' . $studioSchedule->close_time);

            $availableSlots = [];
            $currentTime = $studioOpenTime->copy();
            $slotInterval = 15; // Interval slot dalam menit (bisa disesuaikan)

            // Jika tanggal yang dipilih adalah hari ini, mulai dari jam sekarang + 1 jam
            if ($selectedDate->isToday()) {
                $minStartTime = Carbon::now()->addHour()->startOfHour();
                if ($minStartTime->gt($studioOpenTime)) {
                    $currentTime = $minStartTime;
                }
            }

            while ($currentTime->copy()->addMinutes($duration)->lte($studioCloseTime)) {
                $slotStartTime = $currentTime->copy();
                $slotEndTime = $slotStartTime->copy()->addMinutes($duration);

                // Cek apakah slot ini sudah dibooking
                $isBooked = Booking::where('service', $service->id)
                    ->where('preferred_date', $selectedDate->toDateString())
                    ->where(function ($query) use ($slotStartTime, $slotEndTime) {
                        // Cek overlap dengan booking yang sudah ada
                        $query->where(function ($q) use ($slotStartTime, $slotEndTime) {
                            $q->where('preferred_time', '<', $slotEndTime->format('H:i:s'))
                              ->where('end_time', '>', $slotStartTime->format('H:i:s'));
                        });
                    })
                    ->whereHas('bookingStatus', function ($query) {
                        // Hanya cek booking yang tidak dibatalkan
                        $query->whereNotIn('name', ['Cancelled', 'Rejected']);
                    })
                    ->exists();

                if (!$isBooked) {
                    $availableSlots[] = [
                        'preferred_time_value' => $slotStartTime->format('H:i'),
                        'display_time' => $slotStartTime->format('H:i') . ' - ' . $slotEndTime->format('H:i'),
                    ];
                }

                $currentTime->addMinutes($slotInterval);
            }

            if (empty($availableSlots)) {
                if ($selectedDate->isToday() && Carbon::now()->gt($studioCloseTime)) {
                    return response()->json(['error' => 'Studio operating hours for today have ended.'], 200);
                }
                return response()->json(['error' => 'No available slots for the selected service and date.'], 200);
            }

            return response()->json($availableSlots);

        } catch (\Exception $e) {
            Log::error("Error getAvailableSlots: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load time slots. Please try again.'], 500);
        }
    }

    /**
     * Menyimpan data booking baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone_number' => 'required|string|max:20',
            'service' => 'required|exists:services,id',
            'package' => 'required|exists:packages,id',
            'payment' => 'required|in:dp,full',
            'preferred_date' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(30)->toDateString(),
            'preferred_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $service = Service::findOrFail($request->service);
            $package = Package::findOrFail($request->package);
            $duration = (int) $package->duration_hours;
            
            $bookingDate = Carbon::parse($request->preferred_date);
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $request->preferred_date . ' ' . $request->preferred_time);
            $endTime = $startTime->copy()->addMinutes($duration);

            // Validasi ulang ketersediaan slot (mencegah race condition)
            $isSlotStillAvailable = !Booking::where('service', $service->id)
                ->where('preferred_date', $bookingDate->toDateString())
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('preferred_time', '<', $endTime->format('H:i:s'))
                          ->where('end_time', '>', $startTime->format('H:i:s'));
                    });
                })
                ->whereHas('bookingStatus', function ($query) {
                    $query->whereNotIn('name', ['Cancelled', 'Rejected']);
                })
                ->exists();

            if (!$isSlotStillAvailable) {
                return back()->withErrors(['slot_booked' => 'Sorry, this time slot has just been filled. Please select another slot.'])->withInput();
            }

            // Validasi jam operasional studio
            $studioSchedule = StudioSchedule::where('is_available', true)->first();
            if ($studioSchedule) {
                $studioOpenOnBookingDate = Carbon::parse($bookingDate->toDateString() . ' ' . $studioSchedule->start_time);
                $studioCloseOnBookingDate = Carbon::parse($bookingDate->toDateString() . ' ' . $studioSchedule->end_time);
                
                if ($startTime->lt($studioOpenOnBookingDate) || $endTime->gt($studioCloseOnBookingDate)) {
                    return back()->withErrors(['slot_invalid' => 'Selected booking time is outside studio operating hours.'])->withInput();
                }
            } else {
                return back()->withErrors(['error' => 'Studio schedule not found.'])->withInput();
            }

            // Ambil status booking awal
            $pendingStatus = BookingStatus::where('name', 'Pending')->first();
            if (!$pendingStatus) {
                // Buat status pending jika belum ada
                $pendingStatus = BookingStatus::create([
                    'name' => 'Pending',
                    'description' => 'Booking is pending confirmation',
                    'color' => 'yellow'
                ]);
            }

            // Hitung total harga
            $servicePrice = $service->price;
            $packagePrice = $package->price;
            $totalPrice = $servicePrice + $packagePrice;
            
            // Hitung amount yang harus dibayar berdasarkan pilihan payment
            $paymentAmount = $request->payment === 'dp' ? $totalPrice * 0.5 : $totalPrice;

            $booking = Booking::create([
                'user_id' => auth()->id() ?? null, // Null jika guest booking
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'service' => $request->service,
                'package' => $request->package,
                'preferred_date' => $bookingDate->toDateString(),
                'preferred_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'total_price' => $totalPrice,
                'payment_type' => $request->payment,
                'payment_amount' => $paymentAmount,
                'booking_status_id' => $pendingStatus->id,
                'notes' => $request->notes,
                'payment_due_date' => Carbon::now()->addHours(24), // 24 jam untuk pembayaran
            ]);

            DB::commit();

            // Redirect ke halaman sukses atau detail booking
            return redirect()->route('booking.success', $booking->id)
                ->with('success', 'Booking successful! Your booking ID: ' . $booking->id . '. Please wait for confirmation or proceed with payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error store booking: " . $e->getMessage() . " - Input: " . json_encode($request->all()));
            return back()->withErrors(['error' => 'An error occurred while processing your booking. Please try again later.'])->withInput();
        }
    }

    /**
     * Halaman sukses booking
     */
    public function success(Booking $booking)
    {
        // Pastikan user yang mengakses adalah pemilik booking (untuk user yang login)
        // atau tampilkan untuk semua jika guest booking
        if (auth()->check() && auth()->id() !== $booking->user_id) {
            abort(403, 'Unauthorized access to booking details.');
        }

        $booking->load(['service', 'package', 'bookingStatus']);
        
        return view('pages.booking_success', compact('booking'));
    }

    /**
     * Menampilkan detail booking berdasarkan ID atau booking code
     */
    public function show($id)
    {
        $booking = Booking::with(['service', 'package', 'bookingStatus'])
            ->where('id', $id)
            ->orWhere('booking_code', $id)
            ->firstOrFail();

        // Check authorization jika diperlukan
        if (auth()->check() && auth()->id() !== $booking->user_id) {
            abort(403, 'Unauthorized access to booking details.');
        }

        return view('pages.booking_detail', compact('booking'));
    }

    /**
     * Membatalkan booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Check authorization
        if (auth()->check() && auth()->id() !== $booking->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $cancelledStatus = BookingStatus::where('name', 'Cancelled')->first();
        if (!$cancelledStatus) {
            $cancelledStatus = BookingStatus::create([
                'name' => 'Cancelled',
                'description' => 'Booking has been cancelled',
                'color' => 'red'
            ]);
        }

        $booking->update([
            'booking_status_id' => $cancelledStatus->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason', 'Cancelled by customer')
        ]);

        return redirect()->route('booking.show', $booking->id)
            ->with('success', 'Booking has been cancelled successfully.');
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
             ->join('packages', 'service_packages.package', '=', 'packages.id')
             ->where('service_packages.service', $request->service_id)
             ->where('service_packages.is_active', true)
             ->orderBy('packages.name')
             ->select('packages.id', 'packages.name', 'service_packages.price', 'packages.description')
             ->get();
     
         return response()->json($packages);
     }     
}