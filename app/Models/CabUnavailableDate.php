<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CabUnavailableDate extends Model
{
    protected $fillable = [
        'cab_id',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function cab(): BelongsTo
    {
        return $this->belongsTo(Cab::class);
    }
}
