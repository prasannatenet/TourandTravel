<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'cab_associate_id',
    'name',
    'registration_number',
    'type',
    'capacity',
    'rate_per_km',
    'base_fare',
    'image',
    'status',
    'city_id',
])]
#[Hidden([])]
class Cab extends Model
{
    public function associate(): BelongsTo
    {
        return $this->belongsTo(CabAssociate::class, 'cab_associate_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(CabUnavailableDate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
