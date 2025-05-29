<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    public const PAYMENT_OPTION_DP = 'dp';
    public const PAYMENT_OPTION_FULL = 'full_payment';
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'service_id',
        'package_id',
        'studio_schedule_id',
        'booking_status_id',
        'total_price',
        'notes',
        'booking_date',
        'payment_option',
        'down_payment_amount',
        'payment_status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'total_price' => 'decimal:2',
        'down_payment_amount' => 'decimal:2',
        'booking_date' => 'datetime',
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function studioSchedule(): BelongsTo
    {
        return $this->belongsTo(StudioSchedule::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bookingStatus(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class);
    }

    /**
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BK-' . strtoupper(uniqid());
            }
        });
    }
}