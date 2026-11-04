<?php

namespace Tests\Feature;

use App\Mail\BookingQuotationMail;
use App\Models\Booking;
use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\PackageInclusion;
use App\Models\PackageInclusionPackage;
use App\Models\PackagePlanItem;
use App\Models\State;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminBookingProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_processing_tab_lists_only_pending_bookings(): void
    {
        $package = $this->createPackage();
        $pendingBooking = $this->createBooking($package, 'Pending Guest');
        $confirmedBooking = $this->createBooking($package, 'Confirmed Guest', 'confirmed');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.bookings.processing'));

        $response->assertOk()
            ->assertSee('Bookings To Process')
            ->assertSee('Pending Guest')
            ->assertDontSee('Confirmed Guest')
            ->assertSee(route('admin.bookings.process', $pendingBooking));
        $this->assertSame('confirmed', $confirmedBooking->status);
    }

    public function test_processing_a_booking_saves_only_its_itinerary_and_recalculates_its_amount(): void
    {
        $city = $this->createCity('Included');
        $otherCity = $this->createCity('Other');
        $package = $this->createPackage();
        $package->destinations()->create([
            'destination_type' => City::class,
            'destination_id' => $city->id,
        ]);
        $originalPlace = $this->createPlace($city, 'Original Place', 100);
        $replacementPlace = $this->createPlace($city, 'Replacement Place', 50);
        $outsidePlace = $this->createPlace($otherCity, 'Outside Place', 10);
        $originalRoom = $this->createHotelRoom($city, 'Original Hotel', 100);
        $replacementRoom = $this->createHotelRoom($city, 'Replacement Hotel', 200);
        $placePlanItem = PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $originalPlace->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        $hotelPlanItem = PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'hotel_room_id' => $originalRoom->id,
            'item_type' => 'hotel',
            'day_number' => 1,
            'nights' => 1,
            'start_time' => '15:00:00',
        ]);
        $booking = $this->createBooking($package);
        $booking->update([
            'hotel_room_id' => $originalRoom->id,
            'hotel_request_status' => 'requested',
            'hotel_booking_requested_at' => now(),
            'hotel_response' => 'Previous hotel response',
            'hotel_responded_at' => now(),
            'check_in_date' => '2030-01-01',
            'check_out_date' => '2030-01-02',
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.bookings.process', $booking))
            ->assertOk()
            ->assertSee('Replacement Place')
            ->assertSee('Replacement Hotel')
            ->assertDontSee('Outside Place');

        Mail::fake();

        $this->actingAs($admin)
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'current',
                'days' => 2,
                'itinerary' => [
                    ['kind' => 'place', 'selection_id' => $replacementPlace->id, 'day_number' => 2, 'start_time' => '10:00'],
                    ['kind' => 'hotel', 'selection_id' => $replacementRoom->id, 'day_number' => 2, 'start_time' => '15:00', 'nights' => 1],
                ],
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $updatedBooking = $booking->fresh();
        $this->assertSame('pending', $updatedBooking->status);
        $this->assertSame($replacementRoom->id, $updatedBooking->hotel_room_id);
        $this->assertSame('pending', $updatedBooking->hotel_request_status);
        $this->assertNull($updatedBooking->hotel_booking_requested_at);
        $this->assertNull($updatedBooking->hotel_response);
        $this->assertNull($updatedBooking->hotel_responded_at);
        $this->assertSame('2030-01-02', $updatedBooking->check_in_date->toDateString());
        $this->assertSame('2030-01-03', $updatedBooking->check_out_date->toDateString());
        $this->assertSame(2500.0, (float) $updatedBooking->amount);
        $this->assertSame(2, $updatedBooking->itinerary_override['days']);
        $this->assertSame($replacementPlace->id, $updatedBooking->itinerary_override['items'][0]['city_place_id']);
        $this->assertSame($replacementRoom->id, $updatedBooking->itinerary_override['items'][1]['hotel_room_id']);
        $this->assertSame(2, $updatedBooking->itinerary_override['items'][0]['day_number']);
        $this->assertSame('10:00', $updatedBooking->itinerary_override['items'][0]['start_time']);
        Mail::assertSent(BookingQuotationMail::class, function (BookingQuotationMail $mail) use ($updatedBooking): bool {
            $quotationFile = Attachment::fromData(
                fn (): string => $mail->quotationPdf(),
                'booking-quotation-'.$updatedBooking->id.'.pdf',
            )->withMime('application/pdf');

            return $mail->hasTo($updatedBooking->email)
                && $mail->booking->is($updatedBooking)
                && $mail->days === 2
                && $mail->itineraryItems[0]['title'] === 'Replacement Place'
                && $mail->hasAttachment($quotationFile)
                && str_starts_with($mail->quotationPdf(), '%PDF');
        });
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $placePlanItem->id,
            'city_place_id' => $originalPlace->id,
        ]);
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $hotelPlanItem->id,
            'hotel_room_id' => $originalRoom->id,
        ]);
    }

    public function test_processing_rejects_an_itinerary_place_outside_package_destinations(): void
    {
        $city = $this->createCity('Included');
        $otherCity = $this->createCity('Other');
        $package = $this->createPackage();
        $package->destinations()->create([
            'destination_type' => City::class,
            'destination_id' => $city->id,
        ]);
        $originalPlace = $this->createPlace($city, 'Original Place', 100);
        $outsidePlace = $this->createPlace($otherCity, 'Outside Place', 10);
        $originalRoom = $this->createHotelRoom($city, 'Original Hotel', 100);
        $outsideRoom = $this->createHotelRoom($otherCity, 'Outside Hotel', 100);
        PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $originalPlace->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'hotel_room_id' => $originalRoom->id,
            'item_type' => 'hotel',
            'day_number' => 1,
            'nights' => 1,
            'start_time' => '15:00:00',
        ]);
        $booking = $this->createBooking($package);
        Mail::fake();

        $this->actingAs($this->admin())
            ->from(route('admin.bookings.process', $booking))
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'current',
                'days' => 2,
                'itinerary' => [
                    ['kind' => 'place', 'selection_id' => $outsidePlace->id, 'day_number' => 1, 'start_time' => '09:00'],
                    ['kind' => 'hotel', 'selection_id' => $outsideRoom->id, 'day_number' => 1, 'start_time' => '15:00', 'nights' => 1],
                ],
            ])
            ->assertSessionHasErrors(['itinerary.0.selection_id', 'itinerary.1.selection_id']);

        $this->assertNull($booking->fresh()->itinerary_override);
        Mail::assertNothingSent();
        $this->assertDatabaseHas('package_plan_items', [
            'tour_package_id' => $package->id,
            'city_place_id' => $originalPlace->id,
        ]);
        $this->assertDatabaseHas('package_plan_items', [
            'tour_package_id' => $package->id,
            'hotel_room_id' => $originalRoom->id,
        ]);
    }

    public function test_processing_rejects_an_itinerary_day_after_the_selected_booking_duration(): void
    {
        $city = $this->createCity('Included');
        $package = $this->createPackage();
        $place = $this->createPlace($city, 'Place', 100);
        PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $place->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        $booking = $this->createBooking($package);

        $this->actingAs($this->admin())
            ->from(route('admin.bookings.process', $booking))
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'current',
                'days' => 2,
                'itinerary' => [['kind' => 'place', 'selection_id' => $place->id, 'day_number' => 3, 'start_time' => '09:00']],
            ])
            ->assertSessionHasErrors('itinerary.0.day_number');

        $this->assertNull($booking->fresh()->itinerary_override);
    }

    public function test_processing_can_add_and_remove_booking_only_itinerary_items_and_extend_its_days(): void
    {
        $city = $this->createCity('Included');
        $package = $this->createPackage();
        $package->destinations()->create([
            'destination_type' => City::class,
            'destination_id' => $city->id,
        ]);
        $removedPlace = $this->createPlace($city, 'Removed Place', 10);
        $addedPlace = $this->createPlace($city, 'Added Place', 50);
        $planItem = PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $removedPlace->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        $inclusion = PackageInclusion::create(['name' => 'Airport Transfer']);
        $packageService = PackageInclusionPackage::create([
            'tour_package_id' => $package->id,
            'package_inclusion_id' => $inclusion->id,
            'quantity' => 1,
        ]);
        $booking = $this->createBooking($package);

        $this->actingAs($this->admin())
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'current',
                'days' => 3,
                'itinerary' => [
                    ['kind' => 'place', 'selection_id' => $addedPlace->id, 'day_number' => 3, 'start_time' => '10:00'],
                    ['kind' => 'inclusion', 'selection_id' => $packageService->id, 'day_number' => 3, 'start_time' => '12:00'],
                ],
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $updatedBooking = $booking->fresh();
        $this->assertSame(3, $updatedBooking->itinerary_override['days']);
        $this->assertCount(2, $updatedBooking->itinerary_override['items']);
        $this->assertSame($addedPlace->id, $updatedBooking->itinerary_override['items'][0]['city_place_id']);
        $this->assertSame($packageService->id, $updatedBooking->itinerary_override['items'][1]['package_inclusion_item_id']);
        $this->assertSame(2100.0, (float) $updatedBooking->amount);
        $this->assertSame(2, $package->fresh()->days);
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $planItem->id,
            'city_place_id' => $removedPlace->id,
        ]);
        $this->actingAs($this->admin())
            ->get(route('admin.bookings.process', $booking))
            ->assertOk()
            ->assertSee('Added Place');

        $this->actingAs($this->admin())
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'current',
                'days' => 1,
                'itinerary' => [
                    ['kind' => 'place', 'selection_id' => $addedPlace->id, 'day_number' => 1, 'start_time' => '10:00'],
                ],
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $shrunkBooking = $booking->fresh();
        $this->assertSame(1, $shrunkBooking->itinerary_override['days']);
        $this->assertCount(1, $shrunkBooking->itinerary_override['items']);
        $this->assertSame($addedPlace->id, $shrunkBooking->itinerary_override['items'][0]['city_place_id']);
        $this->assertSame(2, $package->fresh()->days);
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $planItem->id,
            'city_place_id' => $removedPlace->id,
        ]);
    }

    public function test_processing_rejects_an_included_service_from_another_package(): void
    {
        $package = $this->createPackage();
        $otherPackage = $this->createPackage();
        $inclusion = PackageInclusion::create(['name' => 'Third Party Service']);
        $foreignService = PackageInclusionPackage::create([
            'tour_package_id' => $otherPackage->id,
            'package_inclusion_id' => $inclusion->id,
            'quantity' => 1,
        ]);
        $booking = $this->createBooking($package);

        $this->actingAs($this->admin())
            ->from(route('admin.bookings.process', $booking))
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'current',
                'days' => 2,
                'itinerary' => [
                    ['kind' => 'inclusion', 'selection_id' => $foreignService->id, 'day_number' => 1, 'start_time' => '09:00'],
                ],
            ])
            ->assertSessionHasErrors('itinerary.0.selection_id');

        $this->assertNull($booking->fresh()->itinerary_override);
    }

    public function test_processing_can_switch_to_an_existing_package_without_changing_shared_itineraries(): void
    {
        $sourceCity = $this->createCity('Source');
        $targetCity = $this->createCity('Target');
        $sourcePackage = $this->createPackage();
        $targetPackage = $this->createPackage();
        $targetPackage->update(['base_price' => 2000]);
        $sourcePackage->destinations()->create([
            'destination_type' => City::class,
            'destination_id' => $sourceCity->id,
        ]);
        $targetPackage->destinations()->create([
            'destination_type' => City::class,
            'destination_id' => $targetCity->id,
        ]);
        $sourcePlace = $this->createPlace($sourceCity, 'Source Package Place', 10);
        $targetPlace = $this->createPlace($targetCity, 'Target Package Place', 70);
        $sourcePlanItem = PackagePlanItem::create([
            'tour_package_id' => $sourcePackage->id,
            'city_place_id' => $sourcePlace->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        $targetPlanItem = PackagePlanItem::create([
            'tour_package_id' => $targetPackage->id,
            'city_place_id' => $targetPlace->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '10:00:00',
        ]);
        $booking = $this->createBooking($sourcePackage);

        $this->actingAs($this->admin())
            ->get(route('admin.bookings.process', [
                'booking' => $booking,
                'package_id' => $targetPackage->id,
                'package_mode' => 'existing',
            ]))
            ->assertOk()
            ->assertSee('Target Package Place')
            ->assertSee('Change to another available package')
            ->assertSee('Make changes to this package')
            ->assertSee('Create a complete custom package');

        $this->actingAs($this->admin())
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $targetPackage->id,
                'package_mode' => 'existing',
                'days' => 2,
                'itinerary' => [
                    ['kind' => 'place', 'selection_id' => $targetPlace->id, 'day_number' => 1, 'start_time' => '10:00'],
                ],
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $updatedBooking = $booking->fresh();
        $this->assertSame($targetPackage->id, $updatedBooking->package_id);
        $this->assertSame(4140.0, (float) $updatedBooking->amount);
        $this->assertArrayNotHasKey('custom_package', $updatedBooking->itinerary_override);
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $sourcePlanItem->id,
            'city_place_id' => $sourcePlace->id,
        ]);
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $targetPlanItem->id,
            'city_place_id' => $targetPlace->id,
        ]);
    }

    public function test_custom_package_mode_starts_with_an_empty_itinerary_and_keeps_catalog_options_available(): void
    {
        $city = $this->createCity('Custom');
        $package = $this->createPackage();
        $place = $this->createPlace($city, 'Available Custom Place', 50);
        PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $place->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        $booking = $this->createBooking($package);

        $this->actingAs($this->admin())
            ->get(route('admin.bookings.process', [
                'booking' => $booking,
                'package_mode' => 'custom',
            ]))
            ->assertOk()
            ->assertSee('const initialItems = [];', false)
            ->assertSee('Available Custom Place')
            ->assertSee('Package description')
            ->assertSee('Adult price per person (Rs.)')
            ->assertSee('Destinations')
            ->assertSee('Departure date')
            ->assertSee('Countries')
            ->assertSee('States')
            ->assertSee('Cities')
            ->assertDontSee('custom_nights');
    }

    public function test_processing_saves_a_private_custom_package_with_its_own_fare_and_destinations(): void
    {
        $templateCity = $this->createCity('Template');
        $customCity = $this->createCity('Custom');
        $package = $this->createPackage();
        $package->destinations()->create([
            'destination_type' => City::class,
            'destination_id' => $templateCity->id,
        ]);
        $customPlace = $this->createPlace($customCity, 'Private Trip Place', 50);
        $customRoom = $this->createHotelRoom($customCity, 'Private Trip Hotel', 200);
        $service = PackageInclusion::create(['name' => 'Private Trip Transfer']);
        $templatePlace = $this->createPlace($templateCity, 'Template Place', 10);
        $planItem = PackagePlanItem::create([
            'tour_package_id' => $package->id,
            'city_place_id' => $templatePlace->id,
            'item_type' => 'place',
            'day_number' => 1,
            'start_time' => '09:00:00',
        ]);
        $booking = $this->createBooking($package);

        $this->actingAs($this->admin())
            ->put(route('admin.bookings.process.update', $booking), [
                'package_id' => $package->id,
                'package_mode' => 'custom',
                'custom_package_name' => 'Private Jaipur Family Plan',
                'custom_base_price' => 600,
                'custom_category' => 'Family',
                'custom_tour_type' => 'private',
                'custom_description' => 'A private family trip.',
                'custom_departure_city' => 'Delhi',
                'custom_child_price' => 300,
                'custom_infant_price' => 100,
                'custom_discount_percentage' => 10,
                'custom_tax_percentage' => 5,
                'custom_departure_date' => '2030-04-15',
                'custom_destinations' => [
                    'country' => [$customCity->state->country_id],
                    'state' => [$customCity->state_id],
                    'city' => [$customCity->id],
                ],
                'days' => 3,
                'itinerary' => [
                    ['kind' => 'place', 'selection_id' => $customPlace->id, 'day_number' => 3, 'start_time' => '10:00'],
                    ['kind' => 'hotel', 'selection_id' => $customRoom->id, 'day_number' => 3, 'start_time' => '15:00', 'nights' => 1],
                    ['kind' => 'inclusion', 'selection_id' => $service->id, 'day_number' => 3, 'start_time' => '17:00'],
                ],
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $updatedBooking = $booking->fresh();
        $this->assertSame($package->id, $updatedBooking->package_id);
        $this->assertSame('Private Jaipur Family Plan', $updatedBooking->itinerary_override['custom_package']['name']);
        $this->assertSame(600.0, $updatedBooking->itinerary_override['custom_package']['base_price']);
        $this->assertSame('Family', $updatedBooking->itinerary_override['custom_package']['category']);
        $this->assertSame('private', $updatedBooking->itinerary_override['custom_package']['tour_type']);
        $this->assertSame('A private family trip.', $updatedBooking->itinerary_override['custom_package']['description']);
        $this->assertSame('Delhi', $updatedBooking->itinerary_override['custom_package']['departure_city']);
        $this->assertSame(10.0, $updatedBooking->itinerary_override['custom_package']['discount_percentage']);
        $this->assertSame(5.0, $updatedBooking->itinerary_override['custom_package']['tax_percentage']);
        $this->assertSame('2030-04-15', $updatedBooking->itinerary_override['custom_package']['departure_date']);
        $this->assertSame([$customCity->state->country_id], $updatedBooking->itinerary_override['custom_package']['destinations']['country']);
        $this->assertSame([$customCity->state_id], $updatedBooking->itinerary_override['custom_package']['destinations']['state']);
        $this->assertSame([$customCity->id], $updatedBooking->itinerary_override['custom_package']['destinations']['city']);
        $this->assertSame('2030-04-17', $updatedBooking->check_in_date->toDateString());
        $this->assertSame('2030-04-18', $updatedBooking->check_out_date->toDateString());
        $this->assertSame(3, $updatedBooking->itinerary_override['days']);
        $this->assertSame($customPlace->id, $updatedBooking->itinerary_override['items'][0]['city_place_id']);
        $this->assertSame($customRoom->id, $updatedBooking->itinerary_override['items'][1]['hotel_room_id']);
        $this->assertSame($service->id, $updatedBooking->itinerary_override['items'][2]['package_inclusion_id']);
        $this->assertSame(1606.5, (float) $updatedBooking->amount);
        $this->assertSame(2, $package->fresh()->days);
        $this->assertSame(1000.0, (float) $package->fresh()->base_price);
        $this->assertDatabaseHas('package_plan_items', [
            'id' => $planItem->id,
            'tour_package_id' => $package->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('Private Jaipur Family Plan');
    }

    public function test_booking_edits_recalculate_custom_package_fare_from_its_saved_base_price(): void
    {
        $package = $this->createPackage();
        $booking = $this->createBooking($package);
        $booking->update([
            'itinerary_override' => [
                'days' => 2,
                'items' => [],
                'custom_package' => [
                    'name' => 'Private Trip',
                    'base_price' => 600,
                ],
            ],
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.bookings.update', $booking), [
                'package_id' => $package->id,
                'status' => 'pending',
                'adult_count' => 3,
                'child_count' => 0,
                'student_count' => 0,
                'room_count' => 1,
                'extra_bed_count' => 0,
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $updatedBooking = $booking->fresh();
        $this->assertSame(1800.0, (float) $updatedBooking->amount);
        $this->assertSame('Private Trip', $updatedBooking->itinerary_override['custom_package']['name']);
        $this->assertSame(600.0, $updatedBooking->itinerary_override['custom_package']['base_price']);
    }

    public function test_non_admin_cannot_open_booking_processing(): void
    {
        $booking = $this->createBooking($this->createPackage());

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.bookings.processing'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.bookings.process', $booking))
            ->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createPackage(): TourPackage
    {
        return TourPackage::create([
            'name' => 'Booking Processing Package',
            'base_price' => 1000,
            'child_price' => 500,
            'discount_percentage' => 0,
            'tax_percentage' => 0,
            'days' => 2,
            'nights' => 1,
            'is_active' => true,
        ]);
    }

    private function createBooking(TourPackage $package, string $name = 'Test Guest', string $status = 'pending'): Booking
    {
        return Booking::create([
            'package_id' => $package->id,
            'name' => $name,
            'email' => 'guest@example.com',
            'mobile' => '123456789',
            'adult_count' => 2,
            'child_count' => 0,
            'student_count' => 0,
            'country' => 'India',
            'status' => $status,
            'room_count' => 1,
            'extra_bed_count' => 0,
            'amount' => 2200,
        ]);
    }

    private function createCity(string $suffix): City
    {
        $country = Country::create(['name' => 'Country '.$suffix]);
        $state = State::create(['country_id' => $country->id, 'name' => 'State '.$suffix]);

        return City::create([
            'state_id' => $state->id,
            'name' => 'City '.$suffix,
            'primary_image' => 'city.jpg',
            'secondary_images' => [],
            'short_description' => 'Destination city.',
        ]);
    }

    private function createPlace(City $city, string $name, int $price): CityPlace
    {
        return CityPlace::create([
            'city_id' => $city->id,
            'name' => $name,
            'place_description' => $name,
            'description' => $name,
            'primary_image' => 'place.jpg',
            'secondary_images' => [],
            'visit_duration_hours' => 2,
            'best_time_to_visit' => 'morning',
            'price_per_person' => $price,
            'price_child' => 10,
            'price_indian' => $price,
            'price_foreigner' => $price,
            'price_student' => $price,
        ]);
    }

    private function createHotelRoom(City $city, string $name, int $adultPrice): HotelRoom
    {
        $hotel = Hotel::create([
            'city_id' => $city->id,
            'name' => $name,
            'star_rating' => 4,
            'adult_cost' => 100,
            'child_cost' => 50,
            'extra_bed_cost' => 25,
            'address' => 'Main Road',
        ]);
        $roomType = HotelRoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
        ]);

        return HotelRoom::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'adult_cost' => 100,
            'child_cost' => 50,
            'extra_bed_cost' => 25,
            'customer_adult_price' => $adultPrice,
            'customer_child_price' => 50,
            'customer_extra_bed_price' => 25,
        ]);
    }
}
