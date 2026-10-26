<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackageInclusion extends Model
{
    protected $fillable = [
        'name',
        'place_from',
        'place_to',
        'description',
        'scheduled_time',
        'image_one',
        'image_two',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'package_inclusion_package', 'package_inclusion_id', 'tour_package_id');
    }
}
