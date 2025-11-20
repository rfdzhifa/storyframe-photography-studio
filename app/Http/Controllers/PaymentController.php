<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingSuccessMail;

class PaymentController extends Controller
{
    /**
     * Halaman Payment Checkout
     * Tampilkan form/halaman untuk payment (Midtrans Snap, dll)
     */

public function checkout(Booking $booking)
{
    if ($booking->payment_status !== 'pending') {
        return redirect()->route('booking.index')
            ->with('error', 'Booking sudah diproses.');
    }

    if ($booking->booking_status_id == 4) {
        return redirect()->route('booking.index')
            ->with('error', 'Booking dibatalkan.');
    }

    $booking->load(['service', 'package', 'bookingStatus']);

    $amountToPay = $booking->payment_option === 'dp' 
        ? $booking->down_payment_amount 
        : $booking->total_price;

    \Midtrans\Config::$serverKey = config('midtrans.server_key');
    \Midtrans\Config::$isProduction = config('midtrans.is_production');
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    $params = [
        'transaction_details' => [
            'order_id' => $booking->booking_code,
            'gross_amount' => (int) $amountToPay,
        ],
        'customer_details' => [
            'first_name' => $booking->customer_name,
            'email' => $booking->customer_email,
            'phone' => $booking->customer_phone,
        ],
        'item_details' => [[
            'id' => $booking->id,
            'price' => (int) $amountToPay,
            'quantity' => 1,
            'name' => $booking->service->name . ' - ' . $booking->package->name,
        ]]
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return view('pages.payment', [
            'snapToken' => $snapToken,
            'clientKey' => config('midtrans.client_key'),
            'booking' => $booking,
            'orderId' => $booking->booking_code,
            'amount' => $amountToPay,
        ]);
    } catch (\Exception $e) {
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    /**
     * Create Payment Transaction
     * Generate Snap Token (Midtrans), Invoice (Xendit), atau payment method lain
     */
    public function createTransaction(Request $request, Booking $booking)
    {
        try {
            // Validasi booking masih pending
            if ($booking->payment_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking ini sudah diproses.'
                ], 400);
            }

            // Hitung amount
            $amountToPay = $booking->payment_option === 'dp' 
                ? $booking->down_payment_amount 
                : $booking->total_price;

            // ========================================
            // TODO: INTEGRATE PAYMENT GATEWAY DI SINI
            // ========================================
            
            // CONTOH UNTUK MIDTRANS:
            /*
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => (int) $amountToPay,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $booking->customer_email,
                    'phone' => $booking->customer_phone,
                ],
                'item_details' => [
                    [
                        'id' => $booking->service_id,
                        'price' => (int) $amountToPay,
                        'quantity' => 1,
                        'name' => $booking->service->name . ' - ' . $booking->package->name,
                    ]
                ],
                'callbacks' => [
                    'finish' => route('booking.payment.success', $booking->booking_code),
                    'error' => route('booking.payment.failed', $booking->booking_code),
                    'pending' => route('booking.payment.checkout', $booking->booking_code),
                ]
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'booking_code' => $booking->booking_code,
            ]);
            */

            // SEMENTARA: Return dummy response untuk testing
            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'snap_token' => 'DUMMY_SNAP_TOKEN_' . $booking->booking_code,
                'booking_code' => $booking->booking_code,
                'amount' => $amountToPay,
            ]);

        } catch (\Exception $e) {
            Log::error("Create payment transaction error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment Success Handler
     * Dipanggil setelah customer berhasil bayar
     * Bisa dari redirect atau webhook payment gateway
     */
    public function paymentSuccess(Request $request, Booking $booking)
    {
        DB::beginTransaction();

        try {
            // ========================================
            // TODO: VALIDASI SIGNATURE/CALLBACK DARI PAYMENT GATEWAY
            // ========================================
            
            // CONTOH UNTUK MIDTRANS:
            /*
            $serverKey = config('midtrans.server_key');
            $hashed = hash('sha512', 
                $request->order_id . 
                $request->status_code . 
                $request->gross_amount . 
                $serverKey
            );

            if ($hashed !== $request->signature_key) {
                throw new \Exception('Invalid signature');
            }
            */

            // Pastikan booking masih pending
            if ($booking->payment_status !== 'pending') {
                DB::rollBack();
                return redirect()->route('booking.success', $booking->booking_code)
                    ->with('info', 'Payment sudah diproses sebelumnya.');
            }

            // Update payment status
            if ($booking->payment_option === 'dp') {
                $booking->payment_status = 'dp_paid'; // DP sudah dibayar
            } else {
                $booking->payment_status = 'paid'; // Full payment
            }

            // Update booking status menjadi CONFIRMED (ID = 2)
            $booking->booking_status_id = 2; // Confirmed

            // Simpan waktu pembayaran
            $booking->paid_at = Carbon::now();

            $booking->save();

            DB::commit();

            // Kirim email konfirmasi
            try {
                Mail::to($booking->customer_email)->send(new BookingSuccessMail($booking));
            } catch (\Exception $e) {
                // Log error tapi jangan rollback booking
                Log::error("Email gagal dikirim ke {$booking->customer_email}: " . $e->getMessage());
            }

            // Log success
            Log::info("Payment success for booking: {$booking->booking_code}");

            // Redirect ke success page
            return redirect()->route('booking.success', $booking->booking_code)
                ->with('success', 'Pembayaran berhasil! Booking Anda telah dikonfirmasi.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Payment success handler error: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->route('booking.payment.checkout', $booking->booking_code)
                ->withErrors(['error' => 'Terjadi kesalahan saat memproses pembayaran.']);
        }
    }

    /**
     * Payment Failed/Cancel Handler
     * Dipanggil ketika customer membatalkan atau payment gagal
     */
    public function paymentFailed(Request $request, Booking $booking)
    {
        DB::beginTransaction();

        try {
            // Hanya update jika masih pending
            if ($booking->payment_status === 'pending') {
                // Update status jadi CANCELLED (ID = 4)
                $booking->booking_status_id = 4; // Cancelled
                $booking->payment_status = 'failed';
                $booking->save();

                Log::info("Payment failed for booking: {$booking->booking_code}");
            }

            DB::commit();

            return redirect()->route('booking.index')
                ->with('error', 'Pembayaran dibatalkan atau gagal. Silakan coba booking lagi.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Payment failed handler error: " . $e->getMessage());

            return redirect()->route('booking.index')
                ->withErrors(['error' => 'Terjadi kesalahan.']);
        }
    }

    /**
     * Webhook Handler dari Payment Gateway
     * Untuk menerima notifikasi langsung dari Midtrans/Xendit/dll
     * PENTING: Route ini TIDAK boleh pakai CSRF protection
     */
    public function webhook(Request $request)
    {
        try {
            // ========================================
            // TODO: VALIDASI WEBHOOK DARI PAYMENT GATEWAY
            // ========================================
            
            // CONTOH UNTUK MIDTRANS:
            /*
            $serverKey = config('midtrans.server_key');
            $hashed = hash('sha512', 
                $request->order_id . 
                $request->status_code . 
                $request->gross_amount . 
                $serverKey
            );

            if ($hashed !== $request->signature_key) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Ambil booking berdasarkan order_id
            $booking = Booking::where('booking_code', $request->order_id)->first();

            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            // Handle berbagai status dari Midtrans
            $transactionStatus = $request->transaction_status;
            $fraudStatus = $request->fraud_status;

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    // Payment berhasil
                    $this->updateBookingSuccess($booking);
                }
            } elseif ($transactionStatus == 'settlement') {
                // Payment berhasil
                $this->updateBookingSuccess($booking);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                // Payment gagal
                $this->updateBookingFailed($booking);
            } elseif ($transactionStatus == 'pending') {
                // Masih pending, tidak perlu update
            }
            */

            Log::info('Webhook received', $request->all());

            return response()->json(['message' => 'Webhook received'], 200);

        } catch (\Exception $e) {
            Log::error("Webhook error: " . $e->getMessage());
            return response()->json(['message' => 'Webhook error'], 500);
        }
    }

    /**
     * Helper: Update booking success dari webhook
     */
    private function updateBookingSuccess(Booking $booking)
    {
        DB::beginTransaction();

        try {
            if ($booking->payment_status === 'pending') {
                // Update payment status
                if ($booking->payment_option === 'dp') {
                    $booking->payment_status = 'dp_paid';
                } else {
                    $booking->payment_status = 'paid';
                }

                // Update booking status
                $booking->booking_status_id = 2; // Confirmed
                $booking->paid_at = Carbon::now();
                $booking->save();

                // Kirim email
                try {
                    Mail::to($booking->customer_email)->send(new BookingSuccessMail($booking));
                } catch (\Exception $e) {
                    Log::error("Email gagal: " . $e->getMessage());
                }
            }

            DB::commit();
            Log::info("Booking confirmed via webhook: {$booking->booking_code}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update booking success error: " . $e->getMessage());
        }
    }

    /**
     * Helper: Update booking failed dari webhook
     */
    private function updateBookingFailed(Booking $booking)
    {
        DB::beginTransaction();

        try {
            if ($booking->payment_status === 'pending') {
                $booking->booking_status_id = 4; // Cancelled
                $booking->payment_status = 'failed';
                $booking->save();
            }

            DB::commit();
            Log::info("Booking cancelled via webhook: {$booking->booking_code}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update booking failed error: " . $e->getMessage());
        }
    }
}