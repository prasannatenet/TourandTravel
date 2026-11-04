<?php

namespace Tests\Feature;

use App\Models\CabAssociate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CabAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_associate_can_mark_an_owned_cab_unavailable_and_available(): void
    {
        $associate = $this->createAssociate('north-star');
        $cab = $associate->cabs()->create([
            'name' => 'North Star Sedan',
            'registration_number' => 'RJ14CAB1234',
            'type' => 'Sedan',
            'capacity' => 4,
            'rate_per_km' => 14.5,
            'base_fare' => 100,
            'status' => 'active',
        ]);
        $date = now()->addDays(5)->toDateString();

        $this->withSession(['cab_associate_id' => $associate->id])
            ->post(route('cab-associate.cabs.availability.update', $cab), [
                'date' => $date,
                'is_available' => false,
            ])
            ->assertRedirect(route('cab-associate.cabs.availability', [
                'cab' => $cab,
                'month' => substr($date, 0, 7),
            ]))
            ->assertSessionHas('success', 'Cab marked as unavailable.');

        $this->assertDatabaseHas('cab_unavailable_dates', [
            'cab_id' => $cab->id,
            'date' => $date,
        ]);

        $this->withSession(['cab_associate_id' => $associate->id])
            ->post(route('cab-associate.cabs.availability.update', $cab), [
                'date' => $date,
                'is_available' => true,
            ])
            ->assertSessionHas('success', 'Cab marked as available.');

        $this->assertDatabaseMissing('cab_unavailable_dates', [
            'cab_id' => $cab->id,
            'date' => $date,
        ]);
    }

    public function test_associate_cannot_change_another_associates_cab_schedule(): void
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
            ->post(route('cab-associate.cabs.availability.update', $cab), [
                'date' => now()->addDays(5)->toDateString(),
                'is_available' => false,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('cab_unavailable_dates', 0);
    }

    private function createAssociate(string $username): CabAssociate
    {
        return CabAssociate::query()->create([
            'name' => 'Cab Associate',
            'username' => $username,
            'password' => 'StrongCabPassword123!',
            'phone' => '9876543210',
            'status' => 'active',
        ]);
    }
}
