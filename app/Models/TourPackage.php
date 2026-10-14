<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'departure_city',
        'base_price',
        'days',
        'nights',
        'views',
        'is_active',
        'image',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'days' => 'integer',
        'nights' => 'integer',
        'views' => 'integer',
        'is_active' => 'boolean',
    ];

    public function destinations(): HasMany
    {
        return $this->hasMany(PackageDestination::class);
    }
}