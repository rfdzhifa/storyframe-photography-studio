<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Package;
use App\Models\StudioSchedule;
use App\Models\BookingStatus;
use App\Models\ServicePackage; // Pastikan model ini di-import
use App\Services\PaymentService; // Import service kamu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Untuk transaksi database
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\Validator; // Untuk validasi
use Illuminate\Support\Str; // Untuk helper string jika diperlukan

class BookingController extends Controller
{
    /**
     * Service untuk mengelola logika pembayaran.
     *
     * @var \App\Services\PaymentService
     */
    protected PaymentService $paymentService;

    /**
     * Constructor untuk meng-inject dependency (PaymentService).
     *
     * @param \App\Services\PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Menampilkan daftar semua booking (misalnya untuk admin).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil semua booking, urutkan berdasarkan yang terbaru, dan sertakan relasi
        $bookings = Booking::with(['service', 'package', 'studioSchedule', 'bookingStatus'])
                            ->latest()
                            ->paginate(15); // Gunakan paginasi

        // Kembalikan view (contoh untuk admin)
        // return view('admin.bookings.index', compact('bookings'));
        // Untuk sementara, jika belum ada view admin, bisa return JSON atau placeholder
        return response()->json($bookings); // Ganti dengan view jika sudah ada
    }

    /**
     * Menampilkan form untuk membuat booking baru.
     * Data dikirim ke view 'bookings.create' (sesuaikan path jika berbeda).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function create()
    {
        // Ambil data yang dibutuhkan untuk form
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        // Untuk jadwal, idealnya dropdown waktu diisi dinamis berdasarkan tanggal yang dipilih.
        // Untuk saat ini, kita asumsikan jadwal diambil semua yang aktif dan belum lewat.
        // Frontend JavaScript akan menangani filtering waktu berdasarkan tanggal.
        $availableSchedules = StudioSchedule::available()
                                            ->where('date', '>=', now()->toDateString())
                                            ->orderBy('date')
                                            ->orderBy('start_time')
                                            ->get();
        
        // Jika view menggunakan nama 'booking.blade.php' dan ada di 'resources/views/booking.blade.php'
        // atau 'resources/views/some_folder/booking.blade.php'
        // Ganti 'bookings.create' dengan path yang benar jika perlu.
        // Dari struktur @extends('app'), kemungkinan view ada di root views atau subfolder.
        // Asumsi nama viewnya adalah 'booking' (sesuai nama file yang diberikan)
        return view('booking', compact('services', 'packages', 'availableSchedules'));
    }

    /**
     * Menyimpan booking baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validasi Input sesuai nama field di Blade
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20', // Sesuaikan max jika perlu
            'service' => 'required|exists:services,id', // 'service' adalah ID dari service
            'package' => 'required|exists:packages,id', // 'package' adalah ID dari package
            'payment' => 'required|in:dp,full', // 'payment' adalah value dari radio button
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|string', // Format "HH:MM-HH:MM"
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            // Kembali ke form dengan error dan input sebelumnya
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput()
                        ->with('scrollToError', true); // Opsional: untuk scroll ke error di frontend
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // 2. Parse preferred_time dan cari StudioSchedule ID
            $timeParts = explode('-', $request->preferred_time);
            if (count($timeParts) !== 2) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Format waktu tidak valid.')->withInput();
            }
            $parsedStartTime = trim($timeParts[0]) . ':00'; // Tambah detik, misal "10:00:00"
            $parsedEndTime = trim($timeParts[1]) . ':00';   // Tambah detik, misal "10:30:00"

            $schedule = StudioSchedule::where('date', $request->preferred_date)
                                       ->where('start_time', $parsedStartTime)
                                       ->where('end_time', $parsedEndTime)
                                       ->lockForUpdate() // Penting untuk mencegah race condition
                                       ->first();

            if (!$schedule) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Jadwal tidak ditemukan untuk tanggal dan waktu yang dipilih.')->withInput();
            }

            if (!$schedule->is_available) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Maaf, jadwal yang dipilih baru saja tidak tersedia. Silakan pilih jadwal lain.')->withInput();
            }

            // 3. Ambil harga dari ServicePackage
            $servicePackage = ServicePackage::where('service_id', $request->service)
                                ->where('package_id', $request->package)
                                ->where('is_active', true) // Pastikan kombinasi aktif
                                ->first();

            if (!$servicePackage) {
                 DB::rollBack();
                 return redirect()->back()->with('error', 'Kombinasi layanan dan paket tidak valid atau tidak aktif.')->withInput();
            }
            $totalPrice = $servicePackage->price;

            // 4. Hitung DP jika diperlukan
            $downPaymentAmount = null;
            $paymentOption = ($request->payment === 'dp') ? Booking::PAYMENT_OPTION_DP : Booking::PAYMENT_OPTION_FULL;
            $paymentStatus = Booking::PAYMENT_STATUS_PENDING; // Asumsi awal selalu pending

            if ($paymentOption === Booking::PAYMENT_OPTION_DP) {
                // Persentase DP dari Blade adalah 50%, di PaymentService default 30%.
                // Kita bisa override atau pastikan PaymentService menggunakan persentase yang benar.
                // Jika ingin 50% sesuai Blade, bisa $this->paymentService->calculateDownPayment($totalPrice, 0.50);
                $downPaymentAmount = $this->paymentService->calculateDownPayment($totalPrice);
            }

            // 5. Ambil Status Booking Awal (misalnya 'Pending')
            $pendingStatus = BookingStatus::where('name', 'Pending')->first();
            if (!$pendingStatus) {
                // Jika seeder belum dijalankan, ini bisa error.
                // Sebaiknya ada fallback atau pastikan seeder dijalankan.
                DB::rollBack();
                Log::error('Default booking status "Pending" not found.');
                return redirect()->back()->with('error', 'Kesalahan sistem: Status booking awal tidak ditemukan. Hubungi admin.')->withInput();
            }

            // 6. Buat Booking Baru
            $booking = Booking::create([
                // booking_code akan dibuat otomatis oleh Model Event (boot method)
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone_number,
                'service_id' => $request->service,
                'package_id' => $request->package,
                'studio_schedule_id' => $schedule->id,
                'booking_status_id' => $pendingStatus->id,
                'total_price' => $totalPrice,
                'notes' => $request->notes,
                'booking_date' => now(), // Tanggal booking dibuat
                'payment_option' => $paymentOption,
                'down_payment_amount' => $downPaymentAmount,
                'payment_status' => $paymentStatus,
            ]);

            // 7. Update Jadwal menjadi tidak tersedia
            $schedule->is_available = false;
            $schedule->save();

            // 8. Commit Transaksi
            DB::commit();

            // 9. Berikan Respons
            // Redirect ke halaman sukses atau halaman detail booking
            // return redirect()->route('booking.success', ['booking' => $booking->id])->with('success', 'Booking berhasil dibuat!');
            // Untuk sementara, redirect ke halaman utama dengan pesan sukses
            return redirect('/')->with('success', 'Booking Anda dengan kode ' . $booking->booking_code . ' berhasil dibuat! Silakan cek email Anda untuk detail selanjutnya.');


        } catch (\Exception $e) {
            // Jika terjadi error, rollback transaksi
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses booking Anda. Silakan coba lagi atau hubungi customer service.')->withInput();
        }
    }

    /**
     * Menampilkan detail satu booking.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Booking $booking)
    {
        // Muat relasi agar datanya lengkap
        $booking->load(['service', 'package', 'studioSchedule', 'bookingStatus']);

        // return view('bookings.show', compact('booking'));
        return response()->json($booking); // Ganti dengan view jika sudah ada
    }

    /**
     * Menampilkan form untuk mengedit booking (misalnya ganti status oleh admin).
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function edit(Booking $booking)
    {
        $statuses = BookingStatus::orderBy('name')->get(); // Ambil semua status untuk dropdown

        // return view('admin.bookings.edit', compact('booking', 'statuses'));
         return response()->json([ // Ganti dengan view jika sudah ada
            'booking' => $booking->load(['service', 'package', 'studioSchedule', 'bookingStatus']),
            'statuses' => $statuses,
        ]);
    }

    /**
     * Mengupdate data booking di database (misalnya ganti status oleh admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Booking $booking)
    {
        // Validasi input (misalnya hanya boleh update status)
        $validator = Validator::make($request->all(), [
            'booking_status_id' => 'required|exists:booking_status,id',
            // Tambahkan validasi lain jika diperlukan (misal: payment_status, notes)
            'payment_status' => 'nullable|in:' . implode(',', [
                Booking::PAYMENT_STATUS_PENDING,
                Booking::PAYMENT_STATUS_PAID,
                Booking::PAYMENT_STATUS_PARTIALLY_PAID,
                Booking::PAYMENT_STATUS_REFUNDED,
            ]),
        ]);

        if ($validator->fails()) {
            // return redirect()->back()->withErrors($validator)->withInput();
            return response()->json(['errors' => $validator->errors()], 422); // Untuk API/AJAX
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
            // Jika status menjadi 'Completed', pastikan pembayaran juga 'Paid'
            if ($completedStatus && $newStatusId == $completedStatus->id) {
                $booking->payment_status = Booking::PAYMENT_STATUS_PAID;
            }

            $booking->save();

            // Logika jika booking dibatalkan: kembalikan jadwal jadi tersedia
            if ($cancelledStatus && $newStatusId == $cancelledStatus->id && $oldStatusId != $cancelledStatus->id) {
                $schedule = $booking->studioSchedule;
                if ($schedule) {
                    $schedule->is_available = true;
                    $schedule->save();
                }
            }

            DB::commit();

            // return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil diperbarui!');
            return response()->json([ // Untuk API/AJAX
                'message' => 'Booking berhasil diperbarui!',
                'booking' => $booking->load('bookingStatus')
            ]);

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Booking update failed: ' . $e->getMessage());
            //  return redirect()->back()->with('error', 'Gagal memperbarui booking.')->withInput();
             return response()->json(['error' => 'Gagal memperbarui booking.'], 500); // Untuk API/AJAX
        }
    }

    /**
     * Menghapus booking dari database (sebaiknya hanya ubah status jadi 'Cancelled').
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Booking $booking)
    {
        // Daripada menghapus, lebih baik membatalkan.
        // Jika method ini dipanggil, kita akan set statusnya menjadi 'Cancelled'.
        $cancelledStatus = BookingStatus::where('name', 'Cancelled')->first();

        if (!$cancelledStatus) {
            Log::error('Status "Cancelled" tidak ditemukan untuk proses pembatalan booking ID: ' . $booking->id);
            // return redirect()->back()->with('error', 'Gagal membatalkan booking: Status "Cancelled" tidak ditemukan.');
            return response()->json(['error' => 'Gagal membatalkan booking: Status "Cancelled" tidak ditemukan.'], 500);
        }

        if ($booking->booking_status_id == $cancelledStatus->id) {
            // return redirect()->back()->with('info', 'Booking sudah dalam status dibatalkan.');
            return response()->json(['message' => 'Booking sudah dalam status dibatalkan.'], 400);
        }

        // Gunakan method update untuk konsistensi logika (misal, mengembalikan jadwal)
        $request = new Request(['booking_status_id' => $cancelledStatus->id]);
        return $this->update($request, $booking);
    }
}
