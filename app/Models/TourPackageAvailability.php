<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPackageAvailability extends Model
{
    protected $fillable = ['tour_package_id', 'departure_date', 'seat_limit', 'adult_price_override'];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'seat_limit' => 'integer',
            'adult_price_override' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }
}
