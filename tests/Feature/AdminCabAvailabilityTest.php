<?php

namespace Tests\Feature;

use App\Models\CabAssociate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCabAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_an_associates_cab_availability_schedule(): void
    {
        $associate = CabAssociate::query()->create([
            'name' => 'North Star Travels',
            'username' => 'north-star',
            'password' => 'StrongCabPassword123!',
            'phone' => '9876543210',
            'status' => 'active',
        ]);
        $cab = $associate->cabs()->create([
            'name' => 'North Star Sedan',
            'registration_number' => 'RJ14CAB1234',
            'type' => 'Sedan',
            'capacity' => 4,
            'rate_per_km' => 14.5,
            'base_fare' => 100,
            'status' => 'active',
        ]);
        $unavailableDate = now()->addDays(5)->toDateString();
        $cab->unavailableDates()->create(['date' => $unavailableDate]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.cabs.availability', [
                'cab' => $cab,
                'month' => substr($unavailableDate, 0, 7),
            ]))
            ->assertOk()
            ->assertSee('North Star Sedan availability')
            ->assertSee('Unavailable')
            ->assertSee('Available')
            ->assertDontSee('name="is_available"', false);
    }
}
