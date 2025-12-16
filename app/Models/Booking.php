<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $booking_code
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property int $service_id
 * @property int $package_id
 * @property int $booking_status_id
 * @property \Illuminate\Support\Carbon $booking_date
 * @property string $start_time
 * @property string $end_time
 * @property float $total_price
 * @property string|null $notes
 * @property string $payment_option
 * @property float|null $down_payment_amount
 * @property string $payment_status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $snap_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Service|null $service
 * @property-read \App\Models\Package|null $package
 * @property-read \App\Models\BookingStatus|null $bookingStatus
 */
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
        'expires_at',
        'snap_token',
    ];


    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'down_payment_amount' => 'float',
        'total_price' => 'float',
        'expires_at' => 'datetime',
        'snap_token' => 'string',
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

    public function scopeNotExpired(Builder $query): Builder
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->where('payment_status', '!=', 'pending') // semua yang bukan pending
              ->orWhere(function ($q2) use ($now) {
                  $q2->where('payment_status', 'pending')
                     ->where(function ($q3) use ($now) {
                         $q3->whereNull('expires_at')        // data lama
                             ->orWhere('expires_at', '>', $now); // pending tapi BELUM expired
                     });
              });
        });
    }


}
