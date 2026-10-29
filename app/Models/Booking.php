<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'adult_count',
        'child_count',
        'photo_id_proof',
        'country',
        'package_id',
        'status',
    ];

    protected $casts = [
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'country' => 'string',
        'status' => 'string',
    ];

    public function package()
    {
        return $this->belongsTo(TourPackage::class);
    }
}
