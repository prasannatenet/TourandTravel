<?php

namespace Tests\Feature;

use App\Models\Cab;
use App\Models\CabAssociate;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CabAssociateCabTest extends TestCase
{
    use RefreshDatabase;

    public function test_associate_can_add_a_cab_to_their_own_fleet(): void
    {
        Storage::fake('public');
        $associate = $this->createAssociate();
        $city = $this->createCity();

        $this->withSession(['cab_associate_id' => $associate->id])
            ->post(route('cab-associate.cabs.store'), [
                'name' => 'North Star Sedan',
                'registration_number' => 'RJ14CAB1234',
                'type' => 'Sedan',
                'city_id' => $city->id,
                'capacity' => 4,
                'rate_per_km' => 14.5,
                'base_fare' => 100,
                'image' => UploadedFile::fake()->image('cab.jpg'),
                'status' => 'active',
            ])
            ->assertRedirect(route('cab-associate.cabs.index'))
            ->assertSessionHas('success', 'Cab added successfully.');

        $cab = Cab::query()->where('registration_number', 'RJ14CAB1234')->firstOrFail();
        $this->assertSame($associate->id, $cab->cab_associate_id);
        $this->assertSame($city->id, $cab->city_id);
        Storage::disk('public')->assertExists($cab->image);
    }

    public function test_associate_cannot_view_or_change_another_associates_cab(): void
    {
        $associate = $this->createAssociate('north-star');
        $otherAssociate = $this->createAssociate('south-star');
        $cab = $otherAssociate->cabs()->create([
            'name' => 'Other Associate Cab',
            'registration_number' => 'RJ14CAB5678',
            'type' => 'SUV',
            'capacity' => 6,
            'rate_per_km' => 20,
            'base_fare' => 100,
            'status' => 'active',
        ]);

        $this->withSession(['cab_associate_id' => $associate->id])
            ->get(route('cab-associate.cabs.edit', $cab))
            ->assertNotFound();

        $this->withSession(['cab_associate_id' => $associate->id])
            ->delete(route('cab-associate.cabs.destroy', $cab))
            ->assertNotFound();

        $this->assertModelExists($cab);
    }

    public function test_associate_dashboard_lists_only_their_own_cabs(): void
    {
        $associate = $this->createAssociate('north-star');
        $otherAssociate = $this->createAssociate('south-star');
        $associate->cabs()->create([
            'name' => 'My Cab',
            'registration_number' => 'RJ14CAB0001',
            'type' => 'Sedan',
            'capacity' => 4,
            'rate_per_km' => 14,
            'base_fare' => 100,
            'status' => 'active',
        ]);
        $otherAssociate->cabs()->create([
            'name' => 'Private Other Cab',
            'registration_number' => 'RJ14CAB0002',
            'type' => 'SUV',
            'capacity' => 6,
            'rate_per_km' => 20,
            'base_fare' => 100,
            'status' => 'active',
        ]);

        $this->withSession(['cab_associate_id' => $associate->id])
            ->get(route('cab-associate.cabs.index'))
            ->assertOk()
            ->assertSee('My Cab')
            ->assertDontSee('Private Other Cab');
    }

    private function createAssociate(string $username = 'north-star'): CabAssociate
    {
        return CabAssociate::query()->create([
            'name' => 'North Star Travels',
            'username' => $username,
            'password' => 'StrongCabPassword123!',
            'phone' => '9876543210',
            'status' => 'active',
        ]);
    }

    private function createCity(): City
    {
        $country = Country::query()->create(['name' => 'India']);
        $state = State::query()->create([
            'country_id' => $country->id,
            'name' => 'Rajasthan',
        ]);

        return City::query()->create([
            'state_id' => $state->id,
            'name' => 'Jaipur',
            'primary_image' => 'cities/jaipur.jpg',
            'short_description' => 'The Pink City.',
        ]);
    }
}
