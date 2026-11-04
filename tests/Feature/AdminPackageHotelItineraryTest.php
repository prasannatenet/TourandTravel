<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\State;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageHotelItineraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_form_offers_hotel_check_in_with_customer_rates(): void
    {
        $city = $this->createCity();
        $room = $this->createHotelRoom($city);

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.packages.create'));

        $response->assertOk();
        $response->assertSee('Add Hotel Check-in');
        $response->assertSee('Hotel <span class="text-red-500">*</span>', false);
        $response->assertSee('Room Type');
        $response->assertSee('data-customer-adult-price', false);
        $response->assertSee('data-plan-hotel', false);
        $response->assertSee('Hotel '.$city->name);
        $response->assertSee('Deluxe · Deluxe Room');
        $response->assertSee('1500.00');
    }

    public function test_admin_can_save_and_edit_a_hotel_check_in_for_a_package(): void
    {
        $city = $this->createCity();
        $room = $this->createHotelRoom($city);
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Hotel Stay Package',
            'tour_type' => 'group',
            'departure_city' => $city->name,
            'base_price' => 10000,
            'discount_percentage' => 0,
            'tax_percentage' => 0,
            'days' => 3,
            'nights' => 2,
            'is_active' => true,
            'destinations' => ['city' => [$city->id]],
            'plan_items' => [[
                'kind' => 'hotel',
                'day_number' => 1,
                'nights' => 2,
                'start_time' => '14:00',
                'hotel_room_id' => $room->id,
            ]],
        ]);

        $response->assertRedirect(route('admin.packages.index'));
        $package = TourPackage::query()->where('name', 'Hotel Stay Package')->firstOrFail();
        $this->assertDatabaseHas('package_plan_items', [
            'tour_package_id' => $package->id,
            'hotel_room_id' => $room->id,
            'item_type' => 'hotel',
            'day_number' => 1,
            'nights' => 2,
            'start_time' => '14:00:00',
            'city_place_id' => null,
        ]);

        $this->get(route('admin.packages.edit', $package))
            ->assertOk()
            ->assertSee('Room Type')
            ->assertSee('Destination City')
            ->assertSee('data-plan-hotel-city', false)
            ->assertSee('data-plan-hotel', false)
            ->assertSee('data-city-id="'.$city->id.'" selected', false)
            ->assertSee('Hotel '.$city->name)
            ->assertSee('Deluxe · Deluxe Room');

        $this->get(route('admin.packages.show', $package))
            ->assertOk()
            ->assertSee('₹13,000.00')
            ->assertSee('Package hotel addition: ₹3,000.00 per adult (₹6,000.00 for 2 adults)');

        $this->get(route('packages.show', $package->slug))
            ->assertOk()
            ->assertSee('Hotel Check-in')
            ->assertSee('Hotel '.$city->name)
            ->assertSee('₹1,500.00 per adult/night')
            ->assertSee('₹800.00 per child/night')
            ->assertSee('₹500.00 per extra bed/night')
            ->assertSee('₹3,000.00 per adult for 2 nights is included in the package price (₹6,000.00 for 2 adults)')
            ->assertSee('₹13,000');
    }

    public function test_hotel_price_is_calculated_per_hotel_stay_nights_not_package_nights(): void
    {
        $city = $this->createCity();
        $room = $this->createHotelRoom($city);
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Hotel Stay Nights Package',
            'tour_type' => 'group',
            'departure_city' => $city->name,
            'base_price' => 10000,
            'discount_percentage' => 0,
            'tax_percentage' => 0,
            'days' => 3,
            'nights' => 2,
            'is_active' => true,
            'destinations' => ['city' => [$city->id]],
            'plan_items' => [[
                'kind' => 'hotel',
                'day_number' => 1,
                'nights' => 1,
                'start_time' => '14:00',
                'hotel_room_id' => $room->id,
            ]],
        ]);

        $response->assertRedirect(route('admin.packages.index'));
        $package = TourPackage::query()->where('name', 'Hotel Stay Nights Package')->firstOrFail();
        $this->assertDatabaseHas('package_plan_items', [
            'tour_package_id' => $package->id,
            'hotel_room_id' => $room->id,
            'item_type' => 'hotel',
            'day_number' => 1,
            'nights' => 1,
            'start_time' => '14:00:00',
            'city_place_id' => null,
        ]);

        // Package is 2 nights long, but the hotel stay is only 1 night, so the
        // hotel charge must be ₹1,500 (1 night) and not ₹3,000 (2 package nights).
        $this->get(route('admin.packages.show', $package))
            ->assertOk()
            ->assertSee('Package hotel addition: ₹1,500.00 per adult (₹3,000.00 for 2 adults)');

        $this->get(route('packages.show', $package->slug))
            ->assertOk()
            ->assertSee('₹1,500.00 per adult for 1 night is included in the package price (₹3,000.00 for 2 adults)');
    }

    public function test_admin_cannot_add_a_hotel_room_outside_the_selected_destination_cities(): void
    {
        $selectedCity = $this->createCity('Selected');
        $otherCity = $this->createCity('Other');
        $room = $this->createHotelRoom($otherCity);

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->from(route('admin.packages.create'))
            ->post(route('admin.packages.store'), [
                'name' => 'Invalid Hotel Package',
                'tour_type' => 'group',
                'departure_city' => $selectedCity->name,
                'base_price' => 10000,
                'discount_percentage' => 0,
                'tax_percentage' => 0,
                'days' => 2,
                'nights' => 1,
                'destinations' => ['city' => [$selectedCity->id]],
                'plan_items' => [[
                    'kind' => 'hotel',
                    'day_number' => 1,
                    'start_time' => '14:00',
                    'hotel_room_id' => $room->id,
                ]],
            ]);

        $response->assertSessionHasErrors([
            'plan_items.0.hotel_room_id' => 'Choose a room with customer rates from one of the selected destination cities.',
        ]);
        $this->assertDatabaseMissing('tour_packages', ['name' => 'Invalid Hotel Package']);
    }

    public function test_admin_cannot_add_a_hotel_room_without_customer_rates(): void
    {
        $city = $this->createCity();
        $room = $this->createHotelRoom($city, false);

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->from(route('admin.packages.create'))
            ->post(route('admin.packages.store'), [
                'name' => 'Unpriced Hotel Package',
                'tour_type' => 'group',
                'departure_city' => $city->name,
                'base_price' => 10000,
                'discount_percentage' => 0,
                'tax_percentage' => 0,
                'days' => 2,
                'nights' => 1,
                'destinations' => ['city' => [$city->id]],
                'plan_items' => [[
                    'kind' => 'hotel',
                    'day_number' => 1,
                    'start_time' => '14:00',
                    'hotel_room_id' => $room->id,
                ]],
            ]);

        $response->assertSessionHasErrors([
            'plan_items.0.hotel_room_id' => 'Choose a room with customer rates from one of the selected destination cities.',
        ]);
        $this->assertDatabaseMissing('tour_packages', ['name' => 'Unpriced Hotel Package']);
    }

    private function createCity(string $suffix = 'Default'): City
    {
        $country = Country::create(['name' => 'Country '.$suffix]);
        $state = State::create(['country_id' => $country->id, 'name' => 'State '.$suffix]);

        return City::create([
            'state_id' => $state->id,
            'name' => 'City '.$suffix,
            'primary_image' => 'city.jpg',
            'secondary_images' => [],
            'short_description' => 'A destination city.',
        ]);
    }

    private function createHotelRoom(City $city, bool $withCustomerRates = true): HotelRoom
    {
        $hotel = Hotel::create([
            'city_id' => $city->id,
            'name' => 'Hotel '.$city->name,
            'star_rating' => 4,
            'adult_cost' => 1000,
            'child_cost' => 500,
            'extra_bed_cost' => 300,
            'address' => 'Main Road',
        ]);
        $roomType = HotelRoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe',
        ]);

        return HotelRoom::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'name' => 'Deluxe Room',
            'description' => 'A comfortable room.',
            'adult_cost' => 1000,
            'child_cost' => 500,
            'extra_bed_cost' => 300,
            'customer_adult_price' => $withCustomerRates ? 1500 : null,
            'customer_child_price' => $withCustomerRates ? 800 : null,
            'customer_extra_bed_price' => $withCustomerRates ? 500 : null,
        ]);
    }
}
