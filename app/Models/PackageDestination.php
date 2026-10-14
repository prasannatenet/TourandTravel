<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PackageDestination extends Model
{
    protected $table = 'package_destination_pivots';

    protected $fillable = [
        'package_id',
        'destination_type',
        'destination_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function destination(): MorphTo
    {
        return $this->morphTo();
    }
}
