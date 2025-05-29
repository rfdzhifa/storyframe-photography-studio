<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'duration_hours',
        'max_photos',
        'includes_editing',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'duration_hours' => 'integer',
        'max_photos' => 'integer',
        'includes_editing' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_packages')
                    ->using(ServicePackage::class)
                    ->withPivot('price', 'is_active')
                    ->withTimestamps();
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}