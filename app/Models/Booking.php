<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public static function suggestedRoomCount(int $adultCount, int $childCount): int
    {
        return max(
            1,
            (int) ceil($adultCount / 2),
            $childCount,
            (int) ceil(($adultCount + $childCount) / 3),
        );
    }

    public static function requiredExtraBedCount(int $adultCount, int $childCount, int $roomCount): int
    {
        return max(
            0,
            $adultCount - ($roomCount * 2),
            $childCount - $roomCount,
            $adultCount + $childCount - ($roomCount * 3),
        );
    }

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'adult_count',
        'child_count',
        'student_count',
        'photo_id_proof',
        'country',
        'package_id',
        'status',
        'check_in_date',
        'check_out_date',
        'room_count',
        'extra_bed_count',
        'hotel_room_id',
        'hotel_request_status',
        'hotel_booking_requested_at',
        'hotel_response',
        'hotel_responded_at',
        'amount',
        'itinerary_override',
    ];

    protected $casts = [
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'student_count' => 'integer',
        'country' => 'string',
        'status' => 'string',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'room_count' => 'integer',
        'extra_bed_count' => 'integer',
        'hotel_room_id' => 'integer',
        'hotel_request_status' => 'string',
        'hotel_booking_requested_at' => 'datetime',
        'hotel_responded_at' => 'datetime',
        'amount' => 'decimal:2',
        'itinerary_override' => 'array',
    ];

    public function package()
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function hotel()
    {
        return $this->hasOneThrough(
            Hotel::class,
            HotelRoom::class,
            'hotel_id',
            'id',
            'hotel_room_id'
        );
    }
}
