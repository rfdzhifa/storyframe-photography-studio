<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;   

class BookingSuccessMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $booking;
    

    /**
     * Create a new message instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
        // $this->successUrl = route('booking.success', ['booking' => $booking]);
    }

    public function build()
    {
        $successUrl = route('booking.success', ['booking' => $this->booking]);
        
        return $this->subject('Booking Berhasil – 📸 ' . $this->booking->booking_code)
                ->view('emails.booking_success')
                ->with([
                    'booking' => $this->booking,
                    'successUrl' => $successUrl,
                ]);
    }
}