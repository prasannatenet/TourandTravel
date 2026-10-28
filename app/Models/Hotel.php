<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Hidden(['password'])]
class Hotel extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'username',
        'password',
        'star_rating',
        'adult_cost',
        'child_cost',
        'extra_bed_cost',
        'address',
        'hotel_policy',
        'room_policy',
        'primary_image',
        'secondary_images',
    ];

    protected function casts(): array
    {
        return [
            'star_rating' => 'integer',
            'adult_cost' => 'decimal:2',
            'child_cost' => 'decimal:2',
            'extra_bed_cost' => 'decimal:2',
            'password' => 'hashed',
            'secondary_images' => 'array',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(HotelRoomType::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }
}
