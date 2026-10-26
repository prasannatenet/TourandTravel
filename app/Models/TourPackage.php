<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TourPackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'tour_type',
        'description',
        'departure_city',
        'base_price',
        'child_price',
        'infant_price',
        'discount_percentage',
        'tax_percentage',
        'days',
        'nights',
        'views',
        'is_active',
        'image',
    ];

    protected static function booted(): void
    {
        static::creating(function (TourPackage $package): void {
            $package->slug = static::uniqueSlug($package->name);
        });

        static::updating(function (TourPackage $package): void {
            if ($package->isDirty('name')) {
                $package->slug = static::uniqueSlug($package->name, $package->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $counter++;
            $slug = $base.'-'.$counter;
        }

        return $slug;
    }

    protected $casts = [
        'base_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'days' => 'integer',
        'nights' => 'integer',
        'views' => 'integer',
        'is_active' => 'boolean',
    ];

    public function destinations(): HasMany
    {
        return $this->hasMany(PackageDestination::class, 'package_id');
    }

    public function inclusions(): BelongsToMany
    {
        return $this->belongsToMany(PackageInclusion::class, 'package_inclusion_package', 'tour_package_id', 'package_inclusion_id')
            ->withPivot(['quantity', 'customized', 'custom_name', 'custom_place_from', 'custom_place_to', 'custom_description', 'custom_scheduled_time', 'custom_duration_hours', 'custom_image_one', 'custom_image_two']);
    }

    public function inclusionItems(): HasMany
    {
        return $this->hasMany(PackageInclusionPackage::class, 'tour_package_id');
    }

    public function planItems(): HasMany
    {
        return $this->hasMany(PackagePlanItem::class, 'tour_package_id')
            ->orderBy('day_number')
            ->orderBy('start_time')
            ->orderBy('position');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(TourPackageImage::class, 'tour_package_id')->orderBy('position');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(TourPackageAvailability::class, 'tour_package_id')->orderBy('departure_date');
    }
}
