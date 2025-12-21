<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\BookingStatus;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReleaseBookingIfUnpaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bookingId;

    /**
     * Create a new job instance.
     *
     * @param  int  $bookingId
     * @return void
     */
    public function __construct($bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Ambil booking berdasarkan ID
            $booking = Booking::find($this->bookingId);

            if ($booking) {
                if ($booking->payment_status === 'pending'
                    && $booking->expires_at
                    && $booking->expires_at->lte(Carbon::now())
                ) {
                    $cancelledStatus = BookingStatus::where('name', 'Cancelled')->first();

                    if ($cancelledStatus) {
                        $booking->update([
                            'payment_status' => 'failed',
                            'booking_status_id' => $cancelledStatus->id,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to process ReleaseBookingIfUnpaid job: ' . $e->getMessage());
        }
    }
}
