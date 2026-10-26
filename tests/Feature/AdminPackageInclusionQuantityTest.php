<?php

namespace Tests\Feature;

use App\Models\PackageInclusion;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageInclusionQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_and_update_an_inclusion_quantity_for_a_package(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $inclusion = PackageInclusion::create(['name' => 'Pickup Car']);

        $createResponse = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'City Tour',
            'departure_city' => 'Mumbai',
            'base_price' => 1200,
            'days' => 2,
            'nights' => 1,
            'inclusions' => [$inclusion->id => 2],
        ]);

        $createResponse->assertRedirect(route('admin.packages.index'));
        $package = TourPackage::where('name', 'City Tour')->firstOrFail();
        $this->assertDatabaseHas('package_inclusion_package', [
            'tour_package_id' => $package->id,
            'package_inclusion_id' => $inclusion->id,
            'quantity' => 2,
        ]);

        $editResponse = $this->get(route('admin.packages.edit', $package));
        $editResponse->assertOk();
        $editResponse->assertSee('name="inclusions['.$inclusion->id.']" value="2"', false);

        $updateResponse = $this->put(route('admin.packages.update', $package), [
            'name' => 'City Tour',
            'departure_city' => 'Mumbai',
            'base_price' => 1200,
            'days' => 2,
            'nights' => 1,
            'inclusions' => [$inclusion->id => 3],
        ]);

        $updateResponse->assertRedirect(route('admin.packages.index'));
        $this->assertDatabaseHas('package_inclusion_package', [
            'tour_package_id' => $package->id,
            'package_inclusion_id' => $inclusion->id,
            'quantity' => 3,
        ]);

        $this->get(route('admin.packages.show', $package))
            ->assertOk()
            ->assertSee('Quantity: 3');
    }

    public function test_admin_cannot_set_a_negative_inclusion_quantity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $inclusion = PackageInclusion::create(['name' => 'Pickup Car']);

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'City Tour',
            'departure_city' => 'Mumbai',
            'base_price' => 1200,
            'days' => 2,
            'nights' => 1,
            'inclusions' => [$inclusion->id => -1],
        ]);

        $response->assertSessionHasErrors('inclusions.'.$inclusion->id);
        $this->assertDatabaseMissing('tour_packages', ['name' => 'City Tour']);
    }

    public function test_admin_cannot_set_quantity_for_an_unknown_inclusion(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'City Tour',
            'departure_city' => 'Mumbai',
            'base_price' => 1200,
            'days' => 2,
            'nights' => 1,
            'inclusions' => [999999 => 1],
        ]);

        $response->assertSessionHasErrors('inclusions.999999');
        $this->assertDatabaseMissing('tour_packages', ['name' => 'City Tour']);
    }
}
