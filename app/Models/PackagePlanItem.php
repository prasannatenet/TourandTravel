<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackagePlanItem extends Model
{
    protected $fillable = [
        'tour_package_id',
        'city_place_id',
        'package_inclusion_id',
        'package_inclusion_package_id',
        'item_type',
        'day_number',
        'start_time',
        'additional_price',
        'show_charge_button',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'additional_price' => 'decimal:2',
            'position' => 'integer',
            'show_charge_button' => 'boolean',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(CityPlace::class, 'city_place_id');
    }

    public function packageInclusion(): BelongsTo
    {
        return $this->belongsTo(PackageInclusion::class, 'package_inclusion_id');
    }

    public function packageInclusionItem(): BelongsTo
    {
        return $this->belongsTo(PackageInclusionPackage::class, 'package_inclusion_package_id');
    }

    public function inclusions(): BelongsToMany
    {
        return $this->belongsToMany(PackageInclusion::class, 'package_inclusion_package_plan_item', 'package_plan_item_id', 'package_inclusion_id');
    }
}
