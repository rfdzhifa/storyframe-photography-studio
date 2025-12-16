<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property int $duration_minutes
 * @property float|null $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Service[] $services
 */
class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
                Service::class,
                'service_packages',
                'package_id',
                'service_id'
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
}
