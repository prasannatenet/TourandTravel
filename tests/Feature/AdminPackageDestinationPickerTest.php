<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\State;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageDestinationPickerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** @return array{country: Country, state: State, city: City} */
    private function hierarchy(string $suffix = 'A'): array
    {
        $country = Country::create(['name' => 'India '.$suffix]);
        $state = State::create(['country_id' => $country->id, 'name' => 'Goa '.$suffix]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Panaji '.$suffix,
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'short_description' => 'Capital city.',
        ]);

        return ['country' => $country, 'state' => $state, 'city' => $city];
    }

    public function test_create_page_renders_single_cascade_selects_for_multi_country(): void
    {
        $a = $this->hierarchy('A');
        $other = $this->hierarchy('B');
        CityPlace::create([
            'city_id' => $a['city']->id,
            'name' => 'Baga Beach',
            'place_description' => 'Beach.',
            'description' => 'Full.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 100,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.packages.create'));

        $response->assertStatus(200);
        $response->assertSee('name="destinations[country][]"', false);
        $response->assertSee('id="package-states"', false);
        $response->assertSee('id="package-cities"', false);
        $response->assertSee('id="package-places"', false);
        $response->assertSee('data-country-id', false);
        $response->assertSee('data-state-id', false);
        $response->assertSee('data-city-id', false);
        // Single states select — no per-country duplicates.
        $this->assertSame(1, substr_count($response->getContent(), 'name="destinations[state][]"'));
        $this->assertSame(1, substr_count($response->getContent(), 'name="destinations[city][]"'));
        $this->assertSame(1, substr_count($response->getContent(), 'name="destinations[place][]"'));
        $response->assertSee($other['country']->name);
    }

    public function test_store_saves_destinations_across_multiple_countries(): void
    {
        $a = $this->hierarchy('A');
        $b = $this->hierarchy('B');
        $placeA = CityPlace::create([
            'city_id' => $a['city']->id,
            'name' => 'Baga Beach',
            'place_description' => 'Beach.',
            'description' => 'Full.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 100,
        ]);

        $response = $this->actingAs($this->admin())->post(route('admin.packages.store'), [
            'name' => 'Goa Getaway',
            'departure_city' => 'Mumbai',
            'base_price' => 500,
            'days' => 3,
            'nights' => 2,
            'destinations' => [
                'country' => [$a['country']->id, $b['country']->id],
                'state' => [$a['state']->id, $b['state']->id],
                'city' => [$a['city']->id, $b['city']->id],
                'place' => [$placeA->id],
            ],
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        $package = TourPackage::where('name', 'Goa Getaway')->firstOrFail();
        $this->assertSame(2, $package->destinations()->where('destination_type', Country::class)->count());
        $this->assertSame(2, $package->destinations()->where('destination_type', State::class)->count());
        $this->assertSame(2, $package->destinations()->where('destination_type', City::class)->count());
        $this->assertSame(1, $package->destinations()->where('destination_type', CityPlace::class)->count());
    }
}
