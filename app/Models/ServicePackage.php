<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ServicePackage extends Pivot
{
    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     *
     * @var string
     */
    protected $table = 'service_packages';

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service',
        'package',
        'price',
        'is_active',
    ];

    /**
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
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
}