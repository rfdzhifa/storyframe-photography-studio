<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function notification(Request $request)
    {
        try {
            $notification = new Notification();

            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraud = $notification->fraud_status;

            $booking = Booking::where('booking_code', $orderId)->firstOrFail();

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $this->updateStatus($booking, 'Pending Payment'); // Atau status lain yang sesuai
                    } else {
                        $this->updateStatus($booking, 'Paid - Full'); // Asumsi full payment untuk CC
                    }
                }
            } else if ($transaction == 'settlement') {
                // Cek apakah DP atau Full
                if ($booking->payment_option == 'dp') {
                    $this->updateStatus($booking, 'Paid - DP');
                } else {
                    $this->updateStatus($booking, 'Paid - Full');
                }
            } else if ($transaction == 'pending') {
                $this->updateStatus($booking, 'Pending Payment');
            } else if ($transaction == 'deny') {
                $this->updateStatus($booking, 'Cancelled');
            } else if ($transaction == 'expire') {
                $this->updateStatus($booking, 'Cancelled');
            } else if ($transaction == 'cancel') {
                $this->updateStatus($booking, 'Cancelled');
            }

            return response()->json(['message' => 'Payment status updated']);

        } catch (\Exception $e) {
            Log::error('Payment Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    private function updateStatus(Booking $booking, $statusName)
    {
        $status = BookingStatus::where('name', $statusName)->first();
        if ($status) {
            $booking->booking_status_id = $status->id;
            $booking->payment_status = strtolower(str_replace(' ', '_', $statusName)); // e.g., paid_full
            $booking->save();
        }
    }
}
