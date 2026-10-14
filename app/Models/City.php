<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class City extends Model
{
    protected $fillable = [
        'state_id',
        'name',
        'slug',
        'primary_image',
        'secondary_images',
        'short_description',
    ];

    protected function casts(): array
    {
        return ['secondary_images' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (City $city): void {
            $city->slug = static::uniqueSlug($city->name);
        });

        static::updating(function (City $city): void {
            if ($city->isDirty('name')) {
                $city->slug = static::uniqueSlug($city->name, $city->id);
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

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(CityPlace::class);
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
