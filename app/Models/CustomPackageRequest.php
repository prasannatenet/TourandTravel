<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomPackageRequest extends Model
{
    protected $fillable = [
        'owner_token',
        'name',
        'departure_city',
        'days',
        'nights',
        'description',
        'itinerary',
        'inclusions',
        'traveler_counts',
        'booking_details',
        'status',
        'admin_response',
        'admin_quote',
        'admin_cost_notes',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'integer',
            'nights' => 'integer',
            'itinerary' => 'array',
            'inclusions' => 'array',
            'traveler_counts' => 'array',
            'booking_details' => 'array',
            'admin_quote' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
