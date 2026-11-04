<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_can_sign_in_with_its_hotel_code_and_password(): void
    {
        $hotel = $this->createHotel();

        $this->get(route('hotel.login'))
            ->assertOk()
            ->assertSee('Hotel ID or username');

        $this->post(route('hotel.login.post'), [
            'login_id' => $hotel->hotel_code,
            'password' => 'StrongHotelPass123!',
        ])->assertRedirect(route('hotel.dashboard'))
            ->assertSessionHas('hotel_id', $hotel->id);

        $this->get(route('hotel.dashboard'))
            ->assertOk()
            ->assertSee('Hotel Partner Portal')
            ->assertSee('Jaipur Heritage Hotel')
            ->assertSee('HTL-000042')
            ->assertSee('jaipur.manager')
            ->assertSee('Jaipur, Rajasthan, India')
            ->assertSee(route('hotel.policies.edit'))
            ->assertSee('Amer Road, Jaipur')
            ->assertDontSee('StrongHotelPass123!');
    }

    public function test_hotel_can_sign_in_with_its_username(): void
    {
        $hotel = $this->createHotel();

        $this->post(route('hotel.login.post'), [
            'login_id' => strtoupper($hotel->username),
            'password' => 'StrongHotelPass123!',
        ])->assertRedirect(route('hotel.dashboard'))
            ->assertSessionHas('hotel_id', $hotel->id);
    }

    public function test_invalid_hotel_credentials_are_rejected(): void
    {
        $hotel = $this->createHotel();

        $this->from(route('hotel.login'))
            ->post(route('hotel.login.post'), [
                'login_id' => $hotel->hotel_code,
                'password' => 'incorrect password',
            ])
            ->assertRedirect(route('hotel.login'))
            ->assertSessionHasErrors([
                'login_id' => 'The hotel ID/username or password is incorrect.',
            ])
            ->assertSessionMissing('hotel_id');
    }

    public function test_hotel_without_a_configured_password_cannot_sign_in(): void
    {
        $hotel = $this->createHotel();
        $hotel->password = null;
        $hotel->save();

        $this->post(route('hotel.login.post'), [
            'login_id' => $hotel->hotel_code,
            'password' => 'StrongHotelPass123!',
        ])->assertSessionHasErrors('login_id')
            ->assertSessionMissing('hotel_id');
    }

    public function test_guest_is_redirected_to_hotel_login_from_dashboard(): void
    {
        $this->get(route('hotel.dashboard'))
            ->assertRedirect(route('hotel.login'));
    }

    public function test_hotel_session_only_displays_the_authenticated_hotel(): void
    {
        $hotel = $this->createHotel();
        $otherHotel = $this->createHotel('Other City Hotel', 'other.manager', 'HTL-000043');

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.dashboard'))
            ->assertOk()
            ->assertSee($hotel->name)
            ->assertDontSee($otherHotel->name);
    }

    public function test_hotel_logout_clears_the_hotel_session(): void
    {
        $hotel = $this->createHotel();

        $this->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.logout'))
            ->assertRedirect(route('hotel.login'))
            ->assertSessionMissing('hotel_id');

        $this->get(route('hotel.dashboard'))
            ->assertRedirect(route('hotel.login'));
    }

    public function test_admin_account_does_not_grant_hotel_portal_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('hotel.dashboard'))
            ->assertRedirect(route('hotel.login'));
    }

    private function createHotel(
        string $name = 'Jaipur Heritage Hotel',
        string $username = 'jaipur.manager',
        string $hotelCode = 'HTL-000042',
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

        $hotel->hotel_code = $hotelCode;
        $hotel->save();

        return $hotel;
    }
}
