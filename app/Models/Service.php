<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'thumb_mime',
        'thumb_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(
                Package::class,
                'service_packages',
                'service_id',
                'package_id'
            )
            ->using(ServicePackage::class)
            ->withPivot([
                'id',
                'price',
                'description',
                'duration_minutes',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
