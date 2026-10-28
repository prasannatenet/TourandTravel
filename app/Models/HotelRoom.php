<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRoom extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'name',
        'adult_cost',
        'child_cost',
        'extra_bed_cost',
        'customer_adult_price',
        'customer_child_price',
        'customer_extra_bed_price',
        'images',
        'description',
        'highlights',
        'key_points',
        'amenities',
    ];

    protected function casts(): array
    {
        return [
            'adult_cost' => 'decimal:2',
            'child_cost' => 'decimal:2',
            'extra_bed_cost' => 'decimal:2',
            'customer_adult_price' => 'decimal:2',
            'customer_child_price' => 'decimal:2',
            'customer_extra_bed_price' => 'decimal:2',
            'images' => 'array',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(HotelRoomType::class, 'room_type_id');
    }
}
