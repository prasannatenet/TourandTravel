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

    public function test_edit_page_uses_unique_form_indexes_for_itinerary_rows_across_days(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $package = TourPackage::create([
            'name' => 'Multi-day itinerary',
            'departure_city' => 'Mumbai',
            'base_price' => 1200,
            'days' => 2,
            'nights' => 1,
        ]);
        $package->planItems()->create([
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00',
            'position' => 1,
        ]);
        $package->planItems()->create([
            'item_type' => 'place',
            'day_number' => 2,
            'start_time' => '10:00',
            'position' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.packages.edit', $package));

        $response->assertOk();
        preg_match_all('/name="plan_items\[(\d+)\]\[kind\]"/', $response->getContent(), $matches);
        $this->assertSame(['0', '1'], $matches[1]);
    }

    public function test_admin_can_set_and_update_an_inclusion_quantity_for_a_package(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $inclusion = PackageInclusion::create(['name' => 'Pickup Car']);

        $createPage = $this->actingAs($admin)->get(route('admin.packages.create'));
        $createPage->assertOk();
        $createPage->assertSee('data-day-inclusion-catalog', false);
        $createPage->assertSee('data-plan-inclusion-details', false);
        $createPage->assertDontSee('data-day-inclusion-details', false);
        $createPage->assertSee('Manage service catalog');
        $createPage->assertSee('Add service to Day 1');
        $createPage->assertDontSee('Add to plan');
        $createPage->assertSee('inclusions[__KEY__][custom_description]', false);
        $createPage->assertSee('inclusions[__KEY__][custom_image_one]', false);
        $createPage->assertSee('data-inclusion-image-input', false);
        $createPage->assertSee('up to 4 MB');
        $createPage->assertSee('Each entry has its own description');

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
        $editResponse->assertSee('Package Inclusions &amp; Amenities', false);
        $editResponse->assertSee('Create services with descriptions and up to two photos');
        $editResponse->assertSee(route('admin.inclusions.create'), false);
        $editResponse->assertSee(route('admin.inclusions.index'), false);
        $editResponse->assertSee('Pickup Car');
        $editResponse->assertSee('data-day-inclusion-catalog', false);
        $editResponse->assertSee('Add service to Day 1');
        $editResponse->assertSee('Available services');
        $editResponse->assertSee('Day 1 Itinerary');
        $editResponse->assertDontSee('@for($d = 1; $d <= $maximumPlanDay; $d++)', false);
        $editResponse->assertDontSee('$dayItems = collect($savedPlanItems)', false);

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
