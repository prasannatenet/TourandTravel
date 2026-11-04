<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'username',
    'password',
    'phone',
    'email',
    'address',
    'city_id',
    'commission_rate',
    'status',
])]
#[Hidden(['password'])]
class CabAssociate extends Model
{
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function cabs(): HasMany
    {
        return $this->hasMany(Cab::class, 'cab_associate_id');
    }
}
