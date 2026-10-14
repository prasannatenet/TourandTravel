<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDestinationHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_create_country_then_state_then_city(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->post(route('admin.countries.store'), ['name' => 'India'])->assertRedirect(route('admin.countries.index'));
        $country = Country::where('name', 'India')->first();
        $this->assertNotNull($country);

        $this->post(route('admin.states.store'), ['country_id' => $country->id, 'name' => 'Goa'])->assertRedirect(route('admin.states.index'));
        $state = State::where('name', 'Goa')->first();
        $this->assertEquals($country->id, $state->country_id);

        $this->post(route('admin.cities.store'), [
            'state_id' => $state->id,
            'name' => 'Panaji',
            'short_description' => 'Beaches and heritage.',
            'primary_image' => UploadedFile::fake()->create('p.jpg', 500, 'image/jpeg'),
            'secondary_images' => [UploadedFile::fake()->create('s1.jpg', 500, 'image/jpeg')],
        ])->assertRedirect(route('admin.cities.index'));

        $this->assertDatabaseHas('cities', ['name' => 'Panaji', 'slug' => 'panaji']);
        $city = City::where('slug', 'panaji')->first();
        $this->assertEquals($state->id, $city->state_id);
        $this->assertEquals('India', $city->state->country->name);
    }

    public function test_state_create_shows_country_dropdown(): void
    {
        $country = Country::create(['name' => 'India']);
        $response = $this->actingAs($this->admin())->get(route('admin.states.create'));
        $response->assertStatus(200);
        $response->assertSee('Select Country');
        $response->assertSee($country->name);
    }

    public function test_city_create_shows_state_dropdown(): void
    {
        $country = Country::create(['name' => 'India']);
        $state = State::create(['country_id' => $country->id, 'name' => 'Goa']);
        $response = $this->actingAs($this->admin())->get(route('admin.cities.create'));
        $response->assertStatus(200);
        $response->assertSee('Select State');
        $response->assertSee($state->name);
    }

    public function test_states_index_filters_by_country_and_search(): void
    {
        $india = Country::create(['name' => 'India']);
        $france = Country::create(['name' => 'France']);
        State::create(['country_id' => $india->id, 'name' => 'Goa']);
        State::create(['country_id' => $india->id, 'name' => 'Kerala']);
        State::create(['country_id' => $france->id, 'name' => 'Provence']);

        $response = $this->actingAs($this->admin())->get(route('admin.states.index', ['country_id' => $india->id]));
        $response->assertStatus(200);
        $response->assertSee('Goa');
        $response->assertSee('Kerala');
        $response->assertDontSee('Provence');

        $searchResponse = $this->actingAs($this->admin())->get(route('admin.states.index', ['search' => 'Ker']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Kerala');
        $searchResponse->assertDontSee('Goa');
    }

    public function test_cities_index_filters_by_country_and_state(): void
    {
        $india = Country::create(['name' => 'India']);
        $goa = State::create(['country_id' => $india->id, 'name' => 'Goa']);
        $kerala = State::create(['country_id' => $india->id, 'name' => 'Kerala']);
        City::create(['state_id' => $goa->id, 'name' => 'Panaji', 'primary_image' => 'x.jpg', 'secondary_images' => [], 'short_description' => 'd']);
        City::create(['state_id' => $kerala->id, 'name' => 'Kochi', 'primary_image' => 'x.jpg', 'secondary_images' => [], 'short_description' => 'd']);

        $response = $this->actingAs($this->admin())->get(route('admin.cities.index', ['country_id' => $india->id, 'state_id' => $goa->id]));
        $response->assertStatus(200);
        $response->assertSee('Panaji');
        $response->assertDontSee('Kochi');
    }
}
