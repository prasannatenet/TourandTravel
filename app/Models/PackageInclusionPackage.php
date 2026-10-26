<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageInclusionPackage extends Model
{
    protected $table = 'package_inclusion_package';

    public $timestamps = false;

    protected $fillable = [
        'tour_package_id',
        'package_inclusion_id',
        'quantity',
        'customized',
        'custom_name',
        'custom_place_from',
        'custom_place_to',
        'custom_description',
        'custom_scheduled_time',
        'custom_duration_hours',
        'custom_image_one',
        'custom_image_two',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'customized' => 'boolean',
            'custom_duration_hours' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function inclusion(): BelongsTo
    {
        return $this->belongsTo(PackageInclusion::class, 'package_inclusion_id');
    }
}
