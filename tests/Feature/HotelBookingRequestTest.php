<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\State;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelBookingRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_can_see_and_confirm_only_its_sent_booking_requests(): void
    {
        [$hotel, $room] = $this->createHotelWithRoom();
        $booking = $this->createBooking($room, 'requested');

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.booking-requests.index'))
            ->assertOk()
            ->assertSee($booking->name)
            ->assertSee('Confirm rooms');

        $this->withSession(['hotel_id' => $hotel->id])
            ->patch(route('hotel.booking-requests.respond', $booking), [
                'hotel_request_status' => 'confirmed',
                'hotel_response' => 'Two rooms are available.',
            ])
            ->assertRedirect(route('hotel.booking-requests.index'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'hotel_request_status' => 'confirmed',
            'hotel_response' => 'Two rooms are available.',
        ]);
        $this->assertNotNull($booking->fresh()->hotel_responded_at);
    }

    public function test_hotel_cannot_see_or_respond_to_another_hotels_booking(): void
    {
        [$hotel, $room] = $this->createHotelWithRoom();
        [$otherHotel] = $this->createHotelWithRoom('Other Hotel');
        $booking = $this->createBooking($room, 'requested');

        $this->withSession(['hotel_id' => $otherHotel->id])
            ->get(route('hotel.booking-requests.index'))
            ->assertOk()
            ->assertDontSee($booking->name);

        $this->withSession(['hotel_id' => $otherHotel->id])
            ->patch(route('hotel.booking-requests.respond', $booking), [
                'hotel_request_status' => 'confirmed',
            ])
            ->assertNotFound();

        $this->assertSame('requested', $booking->fresh()->hotel_request_status);
    }

    public function test_admin_can_send_a_hotel_request_with_booking_dates_and_room_count(): void
    {
        [$hotel, $room] = $this->createHotelWithRoom();
        $booking = $this->createBooking($room, 'pending');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.hotel-booking-requests.send', $booking))
            ->assertRedirect(route('admin.hotel-booking-requests.index'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'hotel_request_status' => 'requested',
            'room_count' => 2,
            'hotel_room_id' => $room->id,
        ]);
        $this->assertNotNull($booking->fresh()->hotel_booking_requested_at);

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.booking-requests.index'))
            ->assertSee('Confirm rooms');
    }

    private function createHotelWithRoom(string $name = 'Jaipur Hotel'): array
    {
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Rajasthan']);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Jaipur',
            'primary_image' => 'jaipur.jpg',
            'secondary_images' => [],
            'short_description' => 'The Pink City.',
        ]);
        $hotel = Hotel::create([
            'city_id' => $city->id,
            'name' => $name,
            'username' => strtolower(str_replace(' ', '.', $name)),
            'password' => 'StrongHotelPass123!',
            'star_rating' => 4,
            'adult_cost' => 0,
            'child_cost' => 0,
            'extra_bed_cost' => 0,
            'address' => 'Jaipur, Rajasthan',
        ]);
        $roomType = $hotel->roomTypes()->create(['name' => 'Deluxe']);
        $room = HotelRoom::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'name' => 'Deluxe Room',
            'description' => 'A comfortable room.',
        ]);

        return [$hotel, $room];
    }

    private function createBooking(HotelRoom $room, string $requestStatus): Booking
    {
        $package = TourPackage::create([
            'name' => 'Jaipur Weekend',
            'category' => 'holiday',
            'tour_type' => 'private',
            'description' => 'A weekend in Jaipur.',
            'departure_city' => 'Delhi',
            'base_price' => 10000,
            'days' => 3,
            'nights' => 2,
            'is_active' => true,
        ]);

        return Booking::create([
            'name' => 'Asha Guest',
            'email' => 'asha@example.com',
            'mobile' => '9876543210',
            'country' => 'India',
            'adult_count' => 2,
            'child_count' => 0,
            'package_id' => $package->id,
            'status' => 'pending',
            'check_in_date' => '2026-12-10',
            'check_out_date' => '2026-12-12',
            'room_count' => 2,
            'hotel_room_id' => $room->id,
            'hotel_request_status' => $requestStatus,
        ]);
    }
}
