<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\PackagePlanItem;
use App\Models\State;
use App\Models\TourPackage;
use App\Models\TourPackageAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageBookingPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_total_uses_child_nationality_and_student_place_rates(): void
    {
        $package = $this->packageWithPlace();

        $this->post(route('packages.book.store', $package->slug), $this->bookingDetails('India'))
            ->assertOk();

        $this->assertDatabaseHas('bookings', [
            'package_id' => $package->id,
            'country' => 'India',
            'adult_count' => 3,
            'child_count' => 2,
            'student_count' => 1,
            'amount' => 690,
        ]);
    }

    public function test_booking_total_uses_foreigner_rates_for_non_indian_adults(): void
    {
        $package = $this->packageWithPlace();

        $this->post(route('packages.book.store', $package->slug), $this->bookingDetails('USA'))
            ->assertOk();

        $this->assertDatabaseHas('bookings', [
            'package_id' => $package->id,
            'country' => 'USA',
            'student_count' => 1,
            'amount' => 890,
        ]);
    }

    public function test_student_count_cannot_exceed_adult_count(): void
    {
        $package = $this->packageWithPlace();
        $details = $this->bookingDetails('India');
        $details['adult_count'] = 1;
        $details['student_count'] = 2;

        $this->post(route('packages.book.store', $package->slug), $details)
            ->assertSessionHasErrors('student_count');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_ten_adults_are_assigned_five_rooms_without_extra_bed_charges(): void
    {
        $package = $this->packageWithHotelRoom();
        $details = $this->bookingDetails('India');
        $details['adult_count'] = 10;
        $details['child_count'] = 0;
        $details['student_count'] = 0;
        $details['departure_date'] = '2030-01-01';

        $this->post(route('packages.book.store', $package->slug), $details)
            ->assertOk();

        $this->assertDatabaseHas('bookings', [
            'package_id' => $package->id,
            'adult_count' => 10,
            'child_count' => 0,
            'room_count' => 5,
            'extra_bed_count' => 0,
            'amount' => 17000,
        ]);
    }

    public function test_fewer_rooms_add_the_required_extra_bed_charge(): void
    {
        $package = $this->packageWithHotelRoom();
        $details = $this->bookingDetails('India');
        $details['adult_count'] = 10;
        $details['child_count'] = 0;
        $details['student_count'] = 0;
        $details['room_count'] = 4;
        $details['departure_date'] = '2030-01-01';

        $this->post(route('packages.book.store', $package->slug), $details)
            ->assertOk();

        $this->assertDatabaseHas('bookings', [
            'package_id' => $package->id,
            'adult_count' => 10,
            'room_count' => 4,
            'extra_bed_count' => 2,
            'amount' => 18000,
        ]);
    }

    public function test_room_count_cannot_exceed_capacity_even_with_extra_beds(): void
    {
        $package = $this->packageWithHotelRoom();
        $details = $this->bookingDetails('India');
        $details['adult_count'] = 10;
        $details['child_count'] = 0;
        $details['student_count'] = 0;
        $details['room_count'] = 3;
        $details['extra_bed_count'] = 3;
        $details['departure_date'] = '2030-01-01';

        $this->post(route('packages.book.store', $package->slug), $details)
            ->assertStatus(422);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_booking_update_recalculates_place_rates_with_student_count(): void
    {
        $package = $this->packageWithPlace();
        $booking = Booking::create([
            'package_id' => $package->id,
            'name' => 'Test Guest',
            'email' => 'guest@example.com',
            'mobile' => '123456789',
            'adult_count' => 3,
            'child_count' => 2,
            'student_count' => 0,
            'country' => 'USA',
            'status' => 'pending',
            'room_count' => 1,
            'extra_bed_count' => 0,
            'amount' => 0,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.bookings.update', $booking), [
                'package_id' => $package->id,
                'status' => 'pending',
                'adult_count' => 3,
                'child_count' => 2,
                'student_count' => 1,
                'room_count' => 1,
                'extra_bed_count' => 0,
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'student_count' => 1,
            'amount' => 890,
        ]);
    }

    private function packageWithPlace(): TourPackage
    {
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Goa']);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Panaji',
            'primary_image' => 'city.jpg',
            'secondary_images' => [],
            'short_description' => 'Capital city.',
        ]);
        $place = CityPlace::create([
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => 'place.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 100,
            'price_child' => 10,
            'price_indian' => 100,
            'price_foreigner' => 200,
            'price_student' => 50,
        ]);
        $package = TourPackage::create([
            'name' => 'Rate Test Package',
            'base_price' => 100,
            'child_price' => 60,
            'is_active' => true,
        ]);
        PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $place->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);

        return $package;
    }

    private function packageWithHotelRoom(): TourPackage
    {
        $package = $this->packageWithPlace();
        $city = CityPlace::where('name', 'Baga Beach')->firstOrFail()->city;
        $hotel = Hotel::create([
            'city_id' => $city->id,
            'name' => 'Rate Test Hotel',
            'star_rating' => 4,
            'adult_cost' => 1000,
            'child_cost' => 500,
            'extra_bed_cost' => 300,
            'address' => 'Main Road',
        ]);
        $roomType = HotelRoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
        ]);
        $room = HotelRoom::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'description' => 'A comfortable room.',
            'adult_cost' => 1000,
            'child_cost' => 500,
            'extra_bed_cost' => 300,
            'customer_adult_price' => 1500,
            'customer_child_price' => 800,
            'customer_extra_bed_price' => 500,
        ]);
        PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'hotel_room_id' => $room->id,
            'item_type' => 'hotel',
            'day_number' => 2,
            'nights' => 1,
            'start_time' => '14:00:00',
        ]);
        TourPackageAvailability::create([
            'tour_package_id' => $package->id,
            'departure_date' => '2030-01-01',
            'seat_limit' => 20,
        ]);

        return $package;
    }

    /** @return array<string, int|string> */
    private function bookingDetails(string $country): array
    {
        return [
            'name' => 'Test Guest',
            'email' => 'guest@example.com',
            'mobile' => '123456789',
            'adult_count' => 3,
            'child_count' => 2,
            'student_count' => 1,
            'country' => $country,
        ];
    }
}
