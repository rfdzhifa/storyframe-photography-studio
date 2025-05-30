<?php

namespace App\Http\Controllers;

use App\Models\{
    Booking,
    Service,
    Package,
    StudioSchedule,
    BookingStatus,
    ServicePackage
};
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Validator
};

class BookingController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // Tampilkan semua booking dengan paginasi
    public function index()
    {
        $bookings = Booking::with(['service', 'package', 'studioSchedule', 'bookingStatus'])
                            ->latest()
                            ->paginate(15);

        return response()->json($bookings);
    }

    // Tampilkan form booking baru
    public function create()
    {
        $services = Service::active()->orderBy('name')->get();
        $packages = Package::active()->orderBy('name')->get();
        $availableSchedules = StudioSchedule::available()
                                            ->where('date', '>=', now()->toDateString())
                                            ->orderBy('date')
                                            ->orderBy('start_time')
                                            ->get();

        return view('pages.booking', compact('services', 'packages', 'availableSchedules'));
    }

    // Simpan booking baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'service' => 'required|exists:services,id',
            'package' => 'required|exists:packages,id',
            'payment' => 'required|in:dp,full',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('scrollToError', true);
        }

        DB::beginTransaction();

        try {
            // Parse waktu preferred_time "HH:MM-HH:MM"
            [$start, $end] = explode('-', $request->preferred_time) + [null, null];
            if (!$start || !$end) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Format waktu tidak valid.')->withInput();
            }
            $startTime = trim($start) . ':00';
            $endTime = trim($end) . ':00';

            $schedule = StudioSchedule::where('date', $request->preferred_date)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->lockForUpdate()
                ->first();

            if (!$schedule || !$schedule->is_available) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Jadwal yang dipilih tidak tersedia.')
                    ->withInput();
            }

            $servicePackage = ServicePackage::where('service_id', $request->service)
                ->where('package_id', $request->package)
                ->where('is_active', true)
                ->first();

            if (!$servicePackage) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Kombinasi layanan dan paket tidak valid.')
                    ->withInput();
            }

            $totalPrice = $servicePackage->price;
            $paymentOption = $request->payment === 'dp' ? Booking::PAYMENT_OPTION_DP : Booking::PAYMENT_OPTION_FULL;
            $paymentStatus = Booking::PAYMENT_STATUS_PENDING;

            $downPaymentAmount = null;
            if ($paymentOption === Booking::PAYMENT_OPTION_DP) {
                $downPaymentAmount = $this->paymentService->calculateDownPayment($totalPrice);
            }

            $pendingStatus = BookingStatus::where('name', 'Pending')->first();
            if (!$pendingStatus) {
                DB::rollBack();
                Log::error('Default booking status "Pending" not found.');
                return redirect()->back()
                    ->with('error', 'Status booking awal tidak ditemukan. Hubungi admin.')
                    ->withInput();
            }

            $booking = Booking::create([
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone_number,
                'service_id' => $request->service,
                'package_id' => $request->package,
                'studio_schedule_id' => $schedule->id,
                'booking_status_id' => $pendingStatus->id,
                'total_price' => $totalPrice,
                'notes' => $request->notes,
                'booking_date' => now(),
                'payment_option' => $paymentOption,
                'down_payment_amount' => $downPaymentAmount,
                'payment_status' => $paymentStatus,
            ]);

            $schedule->is_available = false;
            $schedule->save();

            DB::commit();

            return redirect('/')
                ->with('success', 'Booking berhasil dibuat! Kode booking: ' . $booking->booking_code);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses booking. Silakan coba lagi.')
                ->withInput();
        }
    }

    // Tampilkan detail booking
    public function show(Booking $booking)
    {
        $booking->load(['service', 'package', 'studioSchedule', 'bookingStatus']);
        return response()->json($booking);
    }

    // Tampilkan form edit booking (misal ganti status)
    public function edit(Booking $booking)
    {
        $statuses = BookingStatus::orderBy('name')->get();

        return response()->json([
            'booking' => $booking->load(['service', 'package', 'studioSchedule', 'bookingStatus']),
            'statuses' => $statuses,
        ]);
    }

    // Update booking
    public function update(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'booking_status_id' => 'required|exists:booking_status,id',
            'payment_status' => 'nullable|in:' . implode(',', [
                Booking::PAYMENT_STATUS_PENDING,
                Booking::PAYMENT_STATUS_PAID,
                Booking::PAYMENT_STATUS_PARTIALLY_PAID,
                Booking::PAYMENT_STATUS_REFUNDED,
            ]),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldStatusId = $booking->booking_status_id;
        $newStatusId = $request->booking_status_id;
        $cancelledStatus = BookingStatus::where('name', 'Cancelled')->first();
        $completedStatus = BookingStatus::where('name', 'Completed')->first();

        DB::beginTransaction();
        try {
            $booking->booking_status_id = $newStatusId;
            if ($request->filled('payment_status')) {
                $booking->payment_status = $request->payment_status;
            }
            if ($completedStatus && $newStatusId == $completedStatus->id) {
                $booking->payment_status = Booking::PAYMENT_STATUS_PAID;
            }
            $booking->save();

            // Kalau batalin booking, jadwal dikembaliin ke available
            if ($cancelledStatus && $newStatusId == $cancelledStatus->id && $oldStatusId != $cancelledStatus->id) {
                $schedule = $booking->studioSchedule;
                if ($schedule) {
                    $schedule->is_available = true;
                    $schedule->save();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Booking berhasil diperbarui!',
                'booking' => $booking->load('bookingStatus')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memperbarui booking.'], 500);
        }
    }

    // "Hapus" booking dengan cara ubah status jadi Cancelled
    public function destroy(Booking $booking)
    {
        $cancelledStatus = BookingStatus::where('name', 'Cancelled')->first();

        if (!$cancelledStatus) {
            Log::error('Status "Cancelled" tidak ditemukan untuk booking ID: ' . $booking->id);
            return response()->json(['error' => 'Status "Cancelled" tidak ditemukan.'], 500);
        }

        if ($booking->booking_status_id == $cancelledStatus->id) {
            return response()->json(['message' => 'Booking sudah dibatalkan.'], 400);
        }

        $request = new Request(['booking_status_id' => $cancelledStatus->id]);
        return $this->update($request, $booking);
    }
}