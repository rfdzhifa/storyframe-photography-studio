<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'service_id',
        'package_id', 
        'booking_status_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'notes',
        'payment_option',
        'down_payment_amount',
        'payment_status',
    ];
    

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'down_payment_amount' => 'float',
        'total_price' => 'float',
    ];


    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function bookingStatus()
    {
        return $this->belongsTo(BookingStatus::class);
    }

    // Helper: Cek apakah booking bentrok sama waktu tertentu
    public function overlaps($start, $end)
    {
        return $this->start_time < $end && $this->end_time > $start;
    }
    
    public function getRouteKeyName()
    {
        return 'booking_code';
    }
    
    protected static function booted()
{
    static::creating(function ($booking) {
        if (empty($booking->booking_code)) {
            $booking->booking_code = 'BOOK-' . strtoupper(Str::random(8));
        }
    });
}

}