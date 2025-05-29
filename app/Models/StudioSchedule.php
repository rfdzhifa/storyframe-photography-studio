<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudioSchedule extends Model
{
    use HasFactory;

    /**
     *
     * @var string
     */
    protected $table = 'studio_schedules';

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];

    /**
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}