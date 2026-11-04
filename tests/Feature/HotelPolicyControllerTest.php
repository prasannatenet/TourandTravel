<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelPolicyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_can_view_and_save_both_policies(): void
    {
        $hotel = $this->createHotel();

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.policies.edit'))
            ->assertOk()
            ->assertSee('Hotel Policy')
            ->assertSee('Room Policy')
            ->assertSee('name="hotel_policy"', false)
            ->assertSee('name="room_policy"', false);

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.policies.update'), [
                'hotel_policy' => "Check-in after 2:00 PM.\nNo smoking indoors.",
                'room_policy' => "Maximum 2 guests.\nExtra beds are subject to availability.",
            ])
            ->assertRedirect(route('hotel.policies.edit'))
            ->assertSessionHas('success', 'Hotel and room policies saved successfully.');

        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'hotel_policy' => "Check-in after 2:00 PM.\nNo smoking indoors.",
            'room_policy' => "Maximum 2 guests.\nExtra beds are subject to availability.",
        ]);
    }

    public function test_guest_is_redirected_from_hotel_policies(): void
    {
        $this->get(route('hotel.policies.edit'))
            ->assertRedirect(route('hotel.login'));

        $this->put(route('hotel.policies.update'), [
            'hotel_policy' => 'Unauthorized policy update.',
            'room_policy' => 'Unauthorized room policy update.',
        ])->assertRedirect(route('hotel.login'));
    }

    public function test_hotel_policy_update_does_not_change_another_hotels_policies(): void
    {
        $hotel = $this->createHotel();
        $otherHotel = $this->createHotel('Other Hotel', 'other.manager');
        $otherHotel->update([
            'hotel_policy' => 'Other hotel policy.',
            'room_policy' => 'Other room policy.',
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.policies.update'), [
                'hotel_policy' => 'My hotel policy.',
                'room_policy' => 'My room policy.',
            ])
            ->assertRedirect(route('hotel.policies.edit'));

        $this->assertDatabaseHas('hotels', [
            'id' => $otherHotel->id,
            'hotel_policy' => 'Other hotel policy.',
            'room_policy' => 'Other room policy.',
        ]);
        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'hotel_policy' => 'My hotel policy.',
            'room_policy' => 'My room policy.',
        ]);
    }

    public function test_hotel_policy_content_is_limited_to_ten_thousand_characters(): void
    {
        $hotel = $this->createHotel();

        $this->from(route('hotel.policies.edit'))
            ->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.policies.update'), [
                'hotel_policy' => str_repeat('a', 10001),
                'room_policy' => 'Valid room policy.',
            ])
            ->assertRedirect(route('hotel.policies.edit'))
            ->assertSessionHasErrors('hotel_policy');

        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'hotel_policy' => null,
            'room_policy' => null,
        ]);
    }

    private function createHotel(
        string $name = 'Jaipur Heritage Hotel',
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
            'name' => $name,
            'username' => $username,
            'password' => 'StrongHotelPass123!',
            'star_rating' => 4,
            'adult_cost' => '5000.00',
            'child_cost' => '2500.00',
            'extra_bed_cost' => '1000.00',
            'address' => 'Amer Road, Jaipur, Rajasthan, India',
        ]);

        $hotel->hotel_code = 'HTL-'.str_pad((string) $hotel->id, 6, '0', STR_PAD_LEFT);
        $hotel->save();

        return $hotel;
    }
}
