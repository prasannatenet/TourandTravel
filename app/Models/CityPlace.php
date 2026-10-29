<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CityPlace extends Model
{
    public const BEST_TIMES = ['morning', 'afternoon', 'evening', 'night'];

    protected $fillable = [
        'city_id',
        'name',
        'slug',
        'place_description',
        'description',
        'primary_image',
        'secondary_images',
        'visit_duration_hours',
        'best_time_to_visit',
        'price_per_person',
        'price_indian',
        'price_foreigner',
        'price_student',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'secondary_images' => 'array',
            'visit_duration_hours' => 'decimal:1',
            'price_per_person' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CityPlace $place): void {
            $place->slug = static::uniqueSlug($place->name);
        });

        static::updating(function (CityPlace $place): void {
            if ($place->isDirty('name')) {
                $place->slug = static::uniqueSlug($place->name, $place->id);
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function primaryImageUrl(): string
    {
        return asset('storage/'.$this->primary_image);
    }

    /** @return array<int, string> */
    public function secondaryImageUrls(): array
    {
        return collect($this->secondary_images ?? [])->map(fn (string $p) => asset('storage/'.$p))->all();
    }
}
