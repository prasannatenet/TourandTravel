<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminHotelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_hotel_for_a_city_without_hotel_level_prices(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Rajasthan']);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Jaipur',
            'primary_image' => 'jaipur.jpg',
            'secondary_images' => [],
            'short_description' => 'The Pink City.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.hotels.store'), [
                'country_id' => $country->id,
                'state_id' => $state->id,
                'city_id' => $city->id,
                ...$this->hotelCredentials(),
                'name' => 'Amber Palace Hotel',
                'star_rating' => 5,
                'address' => 'Amer Road, Jaipur, Rajasthan, India',
            ])
            ->assertRedirect(route('admin.hotels.edit', Hotel::firstOrFail()));

        $hotel = Hotel::firstOrFail();
        $this->assertSame('HTL-'.str_pad((string) $hotel->id, 6, '0', STR_PAD_LEFT), $hotel->hotel_code);
        $this->assertSame('jaipur.manager', $hotel->username);
        $this->assertTrue(Hash::check('StrongHotelPass123!', $hotel->password));
        $this->assertArrayNotHasKey('password', $hotel->toArray());

        $this->assertDatabaseHas('hotels', [
            'city_id' => $city->id,
            'name' => 'Amber Palace Hotel',
            'star_rating' => 5,
            'adult_cost' => '0.00',
            'child_cost' => '0.00',
            'extra_bed_cost' => '0.00',
            'address' => 'Amer Road, Jaipur, Rajasthan, India',
        ]);

        $this->get(route('admin.hotels.index'))
            ->assertOk()
            ->assertSee('Amber Palace Hotel')
            ->assertSee('Jaipur, Rajasthan, India')
            ->assertSee($hotel->hotel_code)
            ->assertSee($hotel->username)
            ->assertDontSee('>Adult</th>', false)
            ->assertDontSee('>Child</th>', false)
            ->assertDontSee('>Extra bed</th>', false)
            ->assertSee(route('admin.hotels.show', $hotel));
    }

    public function test_hotel_form_lists_existing_location_options(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Rajasthan']);
        City::create([
            'state_id' => $state->id,
            'name' => 'Jaipur',
            'primary_image' => 'jaipur.jpg',
            'secondary_images' => [],
            'short_description' => 'The Pink City.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.hotels.create'))
            ->assertOk()
            ->assertSee('name="country_id"', false)
            ->assertSee('name="state_id"', false)
            ->assertSee('name="city_id"', false)
            ->assertSee('India')
            ->assertSee('star_rating')
            ->assertDontSee('name="adult_cost"', false)
            ->assertDontSee('name="child_cost"', false)
            ->assertDontSee('name="extra_bed_cost"', false)
            ->assertDontSee('Hotel cost')
            ->assertSee('Full hotel address')
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_admin_can_view_hotel_profile_and_room_details_created_by_hotel_partner(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
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
            'name' => 'Jaipur Hotel',
            'username' => 'jaipur.manager',
            'password' => 'StrongHotelPass123!',
            'star_rating' => 4,
            'adult_cost' => 1000,
            'child_cost' => 500,
            'extra_bed_cost' => 250,
            'address' => 'Jaipur, Rajasthan, India',
            'hotel_policy' => "Check-in after 2:00 PM.\nNo smoking indoors.",
            'room_policy' => "Maximum 2 guests.\nExtra beds are subject to availability.",
            'primary_image' => 'hotels/1/primary/front.jpg',
            'secondary_images' => ['hotels/1/secondary/lobby.jpg'],
        ]);
        $hotel->hotel_code = 'HTL-000042';
        $hotel->save();
        $roomType = HotelRoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Luxury Suite',
            'description' => 'A premium suite with a city view.',
        ]);
        HotelRoom::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'name' => 'Balcony Suite',
            'adult_cost' => '3750.50',
            'child_cost' => '1800.25',
            'extra_bed_cost' => '900.00',
            'images' => ['hotel-rooms/1/balcony.jpg'],
            'description' => 'A spacious suite with a private balcony.',
            'highlights' => "City view\nKing-size bed",
            'key_points' => "Daily cleaning\nNon-smoking",
            'amenities' => "Balcony\nAir conditioning",
        ]);

        $this->actingAs($admin)
            ->get(route('admin.hotels.show', $hotel))
            ->assertOk()
            ->assertSee('Hotel Details')
            ->assertSee('Jaipur Hotel')
            ->assertSee($hotel->hotel_code)
            ->assertSee('jaipur.manager')
            ->assertSee('Jaipur, Rajasthan, India')
            ->assertDontSee('Hotel rates')
            ->assertDontSee('7,000.00')
            ->assertDontSee('5,000.00')
            ->assertSee('Luxury Suite')
            ->assertSee('A premium suite with a city view.')
            ->assertSee('Balcony Suite')
            ->assertSee('A spacious suite with a private balcony.')
            ->assertSee('Per Adult')
            ->assertSee('₹3,750.50')
            ->assertSee('Per Child (0–12 years)')
            ->assertSee('₹1,800.25')
            ->assertSee('Extra Bed')
            ->assertSee('₹900.00')
            ->assertSee('Customer prices')
            ->assertSee('name="customer_adult_price"', false)
            ->assertSee('name="customer_child_price"', false)
            ->assertSee('name="customer_extra_bed_price"', false)
            ->assertSee('These are separate from the hotel’s rates above.')
            ->assertSee('City view')
            ->assertSee('Daily cleaning')
            ->assertSee('Air conditioning')
            ->assertSee('Hotel Photos')
            ->assertSee('Primary Image')
            ->assertSee('hotels/1/primary/front.jpg')
            ->assertSee('Secondary Images')
            ->assertSee('hotels/1/secondary/lobby.jpg')
            ->assertSee('Hotel Policy')
            ->assertSee('Check-in after 2:00 PM.')
            ->assertSee('Room Policy')
            ->assertSee('Maximum 2 guests.')
            ->assertDontSee('StrongHotelPass123!');
    }

    public function test_admin_can_save_customer_prices_without_changing_hotel_rates(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $hotel = $this->createHotelWithRoom();
        $room = $hotel->rooms()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.hotels.rooms.customer-prices.update', [$hotel, $room]), [
                'customer_adult_price' => '5500.00',
                'customer_child_price' => '2750.00',
                'customer_extra_bed_price' => '1250.00',
            ])
            ->assertRedirect(route('admin.hotels.show', $hotel))
            ->assertSessionHas('success', 'Customer prices updated successfully.');

        $room->refresh();
        $this->assertSame('5500.00', $room->customer_adult_price);
        $this->assertSame('2750.00', $room->customer_child_price);
        $this->assertSame('1250.00', $room->customer_extra_bed_price);
        $this->assertSame('3750.50', $room->adult_cost);
        $this->assertSame('1800.25', $room->child_cost);
        $this->assertSame('900.00', $room->extra_bed_cost);

        $this->get(route('admin.hotels.show', $hotel))
            ->assertOk()
            ->assertSee('₹5,500.00')
            ->assertSee('₹2,750.00')
            ->assertSee('₹1,250.00');
    }

    public function test_getting_customer_price_url_redirects_to_hotel_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $hotel = $this->createHotelWithRoom();
        $room = $hotel->rooms()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.hotels.rooms.customer-prices.show', [$hotel, $room]))
            ->assertRedirect(route('admin.hotels.show', $hotel));
    }

    public function test_admin_customer_prices_require_all_three_non_negative_amounts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $hotel = $this->createHotelWithRoom();
        $room = $hotel->rooms()->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.hotels.show', $hotel))
            ->put(route('admin.hotels.rooms.customer-prices.update', [$hotel, $room]), [
                'customer_adult_price' => '5500.00',
                'customer_child_price' => '-1',
            ])
            ->assertRedirect(route('admin.hotels.show', $hotel))
            ->assertSessionHasErrors(['customer_child_price', 'customer_extra_bed_price']);

        $room->refresh();
        $this->assertNull($room->customer_adult_price);
        $this->assertNull($room->customer_child_price);
        $this->assertNull($room->customer_extra_bed_price);
    }

    public function test_admin_cannot_set_customer_prices_for_a_room_under_another_hotel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $hotel = $this->createHotelWithRoom('First Hotel', 'first.manager');
        $otherHotel = $this->createHotelWithRoom('Other Hotel', 'other.manager');
        $otherRoom = $otherHotel->rooms()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.hotels.rooms.customer-prices.update', [$hotel, $otherRoom]), [
                'customer_adult_price' => '5500.00',
                'customer_child_price' => '2750.00',
                'customer_extra_bed_price' => '1250.00',
            ])
            ->assertNotFound();

        $otherRoom->refresh();
        $this->assertNull($otherRoom->customer_adult_price);
    }

    public function test_hotel_creation_rejects_a_city_outside_the_selected_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::create(['name' => 'India']);
        $rajasthan = State::create(['country_id' => $country->id, 'name' => 'Rajasthan']);
        $gujarat = State::create(['country_id' => $country->id, 'name' => 'Gujarat']);
        $jaipur = City::create([
            'state_id' => $rajasthan->id,
            'name' => 'Jaipur',
            'primary_image' => 'jaipur.jpg',
            'secondary_images' => [],
            'short_description' => 'The Pink City.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.hotels.store'), [
                'country_id' => $country->id,
                'state_id' => $gujarat->id,
                'city_id' => $jaipur->id,
                ...$this->hotelCredentials(),
                'name' => 'Incorrect Location Hotel',
                'star_rating' => 3,
                'address' => 'Some address',
            ])
            ->assertSessionHasErrors([
                'city_id' => 'The selected city does not belong to the selected state.',
            ]);

        $this->assertDatabaseCount('hotels', 0);
    }

    public function test_hotel_creation_rejects_a_state_outside_the_selected_country(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $india = Country::create(['name' => 'India']);
        $canada = Country::create(['name' => 'Canada']);
        $state = State::create(['country_id' => $india->id, 'name' => 'Rajasthan']);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Jaipur',
            'primary_image' => 'jaipur.jpg',
            'secondary_images' => [],
            'short_description' => 'The Pink City.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.hotels.store'), [
                'country_id' => $canada->id,
                'state_id' => $state->id,
                'city_id' => $city->id,
                ...$this->hotelCredentials(),
                'name' => 'Incorrect Country Hotel',
                'star_rating' => 3,
                'address' => 'Some address',
            ])
            ->assertSessionHasErrors([
                'state_id' => 'The selected state does not belong to the selected country.',
            ]);

        $this->assertDatabaseCount('hotels', 0);
    }

    public function test_hotel_creation_rejects_out_of_range_stars(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Rajasthan']);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Jaipur',
            'primary_image' => 'jaipur.jpg',
            'secondary_images' => [],
            'short_description' => 'The Pink City.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.hotels.store'), [
                'country_id' => $country->id,
                'state_id' => $state->id,
                'city_id' => $city->id,
                ...$this->hotelCredentials(),
                'name' => 'Invalid Star Rating Hotel',
                'star_rating' => 6,
                'address' => 'Some address',
            ])
            ->assertSessionHasErrors(['star_rating']);

        $this->assertDatabaseCount('hotels', 0);
    }

    public function test_admin_can_update_and_delete_a_hotel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
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
            'name' => 'Old Hotel Name',
            'username' => 'jaipur.manager',
            'password' => 'StrongHotelPass123!',
            'star_rating' => 3,
            'adult_cost' => 1000,
            'child_cost' => 500,
            'extra_bed_cost' => 250,
            'address' => 'Old address',
        ]);
        $originalPassword = $hotel->password;

        $this->actingAs($admin)
            ->get(route('admin.hotels.edit', $hotel))
            ->assertOk()
            ->assertDontSee('name="adult_cost"', false)
            ->assertDontSee('name="child_cost"', false)
            ->assertDontSee('name="extra_bed_cost"', false);

        $this->actingAs($admin)
            ->put(route('admin.hotels.update', $hotel), [
                'country_id' => $country->id,
                'state_id' => $state->id,
                'city_id' => $city->id,
                'username' => 'jaipur.manager',
                'name' => 'Updated Hotel',
                'star_rating' => 4,
                'adult_cost' => '2000.00',
                'child_cost' => '1000.00',
                'extra_bed_cost' => '400.00',
                'address' => 'Updated full address, Jaipur',
            ])
            ->assertRedirect(route('admin.hotels.index'));

        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'name' => 'Updated Hotel',
            'star_rating' => 4,
            'adult_cost' => '1000.00',
            'address' => 'Updated full address, Jaipur',
        ]);
        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'child_cost' => '500.00',
            'extra_bed_cost' => '250.00',
        ]);
        $hotel->refresh();
        $this->assertSame($originalPassword, $hotel->password);

        $this->put(route('admin.hotels.update', $hotel), [
            'country_id' => $country->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'username' => 'jaipur.manager',
            'name' => 'Updated Hotel',
            'star_rating' => 4,
            'address' => 'Updated full address, Jaipur',
            'password' => 'AnotherStrongPass987!',
            'password_confirmation' => 'AnotherStrongPass987!',
        ])->assertRedirect(route('admin.hotels.index'));

        $this->assertTrue(Hash::check('AnotherStrongPass987!', $hotel->fresh()->password));

        $this->delete(route('admin.hotels.destroy', $hotel))
            ->assertRedirect(route('admin.hotels.index'));

        $this->assertDatabaseMissing('hotels', ['id' => $hotel->id]);
    }

    public function test_non_admin_cannot_access_hotel_management(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.hotels.index'))
            ->assertForbidden();
    }

    /**
     * @return array<string, string>
     */
    private function hotelCredentials(): array
    {
        return [
            'username' => 'jaipur.manager',
            'password' => 'StrongHotelPass123!',
            'password_confirmation' => 'StrongHotelPass123!',
        ];
    }

    private function createHotelWithRoom(
        string $hotelName = 'Jaipur Hotel',
        string $username = 'jaipur.manager',
    ): Hotel {
        $country = Country::firstOrCreate(['name' => 'India']);
        $state = State::firstOrCreate(
            ['country_id' => $country->id, 'name' => 'Rajasthan'],
        );
        $city = City::firstOrCreate(
            ['state_id' => $state->id, 'name' => 'Jaipur'],
            [
                'primary_image' => 'jaipur.jpg',
                'secondary_images' => [],
                'short_description' => 'The Pink City.',
            ],
        );
        $hotel = Hotel::create([
            'city_id' => $city->id,
            'name' => $hotelName,
            'username' => $username,
            'password' => 'StrongHotelPass123!',
            'star_rating' => 4,
            'adult_cost' => '3750.50',
            'child_cost' => '1800.25',
            'extra_bed_cost' => '900.00',
            'address' => 'Jaipur, Rajasthan, India',
        ]);
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);
        $hotel->rooms()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'adult_cost' => '3750.50',
            'child_cost' => '1800.25',
            'extra_bed_cost' => '900.00',
            'description' => 'A comfortable room.',
        ]);

        return $hotel;
    }
}
