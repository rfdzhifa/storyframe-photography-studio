<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ServicePackage extends Pivot
{
    protected $table = 'service_packages';

    // karena tabel ini punya kolom id
    public $incrementing = true;

    protected $fillable = [
        'service_id',
        'package_id',
        'price',
        'description',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}
