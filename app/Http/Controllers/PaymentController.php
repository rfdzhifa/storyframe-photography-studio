<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function createTransaction(Request $request)
    {
       try {
            // Log incoming request untuk debugging
            Log::info('Payment request received', [
                'amount' => $request->amount,
                'full_name' => $request->full_name,
                'email' => $request->email,
            ]);

            // Validasi input
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'full_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'nullable|string',
            ]);

            // Set Midtrans Configuration
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Generate unique Order ID
            $orderId = 'BOOKING-' . time() . '-' . rand(1000, 9999);

            // Prepare transaction parameters
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $validated['amount'],
                ],
                'customer_details' => [
                    'first_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone_number'] ?? '',
                ],
                'item_details' => [
                    [
                        'id' => 'booking-' . time(),
                        'price' => (int) $validated['amount'],
                        'quantity' => 1,
                        'name' => 'Booking Payment',
                    ]
                ],
            ];

            Log::info('Midtrans params', $params);

            Log::info('Midtrans config check', [
                'server_key' => config('services.midtrans.server_key'),
                'is_production' => config('services.midtrans.is_production'),
            ]);


            // Get Snap Token from Midtrans
            $snapToken = Snap::getSnapToken($params);

            Log::info('Snap token generated', ['token' => $snapToken]);

            return response()->json([
                'success' => true,
                'snapToken' => $snapToken,
                'order_id' => $orderId,
            ]);

        } catch (\Midtrans\Exceptions\InputValidationException $e) {
            Log::error('Midtrans Input Validation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Data tidak valid',
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Midtrans\Exceptions\ApiException $e) {
            Log::error('Midtrans API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Midtrans API Error',
                'message' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            Log::error('Payment creation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan server',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}