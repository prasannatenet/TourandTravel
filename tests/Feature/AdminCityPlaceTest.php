<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCityPlaceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function city(): City
    {
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Goa']);

        return City::create([
            'state_id' => $state->id,
            'name' => 'Panaji',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'short_description' => 'Capital city.',
        ]);
    }

    public function test_admin_can_create_place_with_all_fields(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $city = $this->city();
        $city->load(['state.country']);

        $response = $this->post(route('admin.places.store'), [
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details about beach, shacks, nightlife.',
            'primary_image' => UploadedFile::fake()->create('p.jpg', 500, 'image/jpeg'),
            'secondary_images' => [UploadedFile::fake()->create('s1.jpg', 500, 'image/jpeg')],
            'visit_duration_hours' => 3.5,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 250,
            'note' => 'Carry sunscreen.',
        ]);

        $response->assertRedirect(route('admin.places.index'));
        $this->assertDatabaseHas('city_places', ['name' => 'Baga Beach', 'slug' => 'baga-beach', 'best_time_to_visit' => 'evening', 'price_per_person' => 250]);
        $place = CityPlace::where('slug', 'baga-beach')->first();
        $this->assertEquals($city->id, $place->city_id);
        $this->assertCount(1, $place->secondary_images);
    }

    public function test_mismatched_cascade_chain_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $city = $this->city();
        $otherCountry = Country::create(['name' => 'France']);

        $this->post(route('admin.places.store'), [
            'country_id' => $otherCountry->id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => UploadedFile::fake()->create('p.jpg', 500, 'image/jpeg'),
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 100,
        ])->assertSessionHasErrors('city_id');
    }

    public function test_best_time_validation_rejects_invalid(): void
    {
        $this->actingAs($this->admin());
        $city = $this->city();
        $city->load(['state.country']);

        $this->post(route('admin.places.store'), [
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'name' => 'X',
            'place_description' => 'd',
            'description' => 'd',
            'primary_image' => UploadedFile::fake()->create('p.jpg', 500, 'image/jpeg'),
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'midnight',
        ])->assertSessionHasErrors('best_time_to_visit');
    }

    public function test_price_per_person_is_required_on_create(): void
    {
        $this->actingAs($this->admin());
        $city = $this->city();
        $city->load(['state.country']);

        $this->post(route('admin.places.store'), [
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => UploadedFile::fake()->create('p.jpg', 500, 'image/jpeg'),
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
        ])->assertSessionHasErrors('price_per_person');
    }

    public function test_negative_price_per_person_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $city = $this->city();
        $city->load(['state.country']);

        $this->post(route('admin.places.store'), [
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => UploadedFile::fake()->create('p.jpg', 500, 'image/jpeg'),
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => -5,
        ])->assertSessionHasErrors('price_per_person');
    }

    public function test_admin_can_update_place_with_cascade_fields(): void
    {
        Storage::fake('public');
        $city = $this->city();
        $city->load(['state.country']);
        $place = CityPlace::create([
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 100,
        ]);

        $response = $this->actingAs($this->admin())->put(route('admin.places.update', $place), [
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Updated full details.',
            'visit_duration_hours' => 3,
            'best_time_to_visit' => 'morning',
            'price_per_person' => 350,
            'note' => 'Updated note.',
        ]);

        $response->assertRedirect(route('admin.places.index'));
        $this->assertDatabaseHas('city_places', [
            'id' => $place->id,
            'description' => 'Updated full details.',
            'best_time_to_visit' => 'morning',
            'price_per_person' => 350,
        ]);
    }

    public function test_update_from_edit_form_includes_country_and_state_names(): void
    {
        $city = $this->city();
        $city->load(['state.country']);
        $place = CityPlace::create([
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'price_per_person' => 100,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.places.edit', $place));
        $response->assertStatus(200);
        $response->assertSee('name="country_id"', false);
        $response->assertSee('name="state_id"', false);
        $response->assertSee('name="city_id"', false);
    }

    public function test_places_index_is_table_with_view_button(): void
    {
        $city = $this->city();
        $place = CityPlace::create([
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.places.index'));
        $response->assertStatus(200);
        $response->assertSee('<table', false);
        $response->assertSee(route('admin.places.show', $place), false);
        $response->assertSee('View');
    }

    public function test_place_detail_view_shows_complete_information(): void
    {
        $city = $this->city();
        $place = CityPlace::create([
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Famous beach.',
            'description' => 'Full details.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'evening',
            'note' => 'Carry sunscreen.',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.places.show', $place));
        $response->assertStatus(200);
        $response->assertSee('Baga Beach');
        $response->assertSee('Complete Information');
        $response->assertSee('Carry sunscreen.');
    }

    public function test_cascade_api_returns_states_then_cities(): void
    {
        $city = $this->city();
        $city->load(['state.country']);
        $this->actingAs($this->admin());

        $statesResponse = $this->get(route('admin.places.states-by-country', $city->state->country_id));
        $statesResponse->assertStatus(200);
        $statesResponse->assertJsonFragment(['name' => 'Goa']);

        $citiesResponse = $this->get(route('admin.places.cities-by-state', $city->state_id));
        $citiesResponse->assertStatus(200);
        $citiesResponse->assertJsonFragment(['name' => 'Panaji']);
    }

    public function test_place_create_shows_country_dropdown(): void
    {
        $city = $this->city();
        $response = $this->actingAs($this->admin())->get(route('admin.places.create'));
        $response->assertStatus(200);
        $response->assertSee('Select Country');
        $response->assertSee($city->state->country->name);
    }

    public function test_places_index_filters_by_cascade_and_best_time(): void
    {
        $city = $this->city();
        $city->load(['state.country']);
        CityPlace::create([
            'city_id' => $city->id,
            'name' => 'Baga Beach',
            'place_description' => 'Beach.',
            'description' => 'Full.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 3,
            'best_time_to_visit' => 'evening',
        ]);
        $otherCountry = Country::create(['name' => 'France']);
        $otherState = State::create(['country_id' => $otherCountry->id, 'name' => 'Provence']);
        $otherCity = City::create(['state_id' => $otherState->id, 'name' => 'Nice', 'primary_image' => 'x.jpg', 'secondary_images' => [], 'short_description' => 'd']);
        CityPlace::create([
            'city_id' => $otherCity->id,
            'name' => 'Promenade',
            'place_description' => 'Sea walk.',
            'description' => 'Full.',
            'primary_image' => 'x.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'morning',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.places.index', [
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Baga Beach');
        $response->assertDontSee('Promenade');

        $timeResponse = $this->actingAs($this->admin())->get(route('admin.places.index', ['best_time_to_visit' => 'morning']));
        $timeResponse->assertStatus(200);
        $timeResponse->assertSee('Promenade');
        $timeResponse->assertDontSee('Baga Beach');
    }
}
