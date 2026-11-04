<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HotelRoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_hotel_room_pages(): void
    {
        $this->get(route('hotel.room-types.index'))
            ->assertRedirect(route('hotel.login'));

        $this->get(route('hotel.rooms.index'))
            ->assertRedirect(route('hotel.login'));
    }

    public function test_hotel_can_create_a_room_type(): void
    {
        $hotel = $this->createHotel();

        $this->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.room-types.store'), [
                'name' => 'Luxury Suite',
                'description' => 'A spacious suite with a private balcony.',
            ])
            ->assertRedirect(route('hotel.room-types.index'))
            ->assertSessionHas('success', 'Room type created successfully.');

        $this->assertDatabaseHas('hotel_room_types', [
            'hotel_id' => $hotel->id,
            'name' => 'Luxury Suite',
            'description' => 'A spacious suite with a private balcony.',
        ]);
    }

    public function test_hotel_cannot_create_a_duplicate_room_type(): void
    {
        $hotel = $this->createHotel();
        $hotel->roomTypes()->create(['name' => 'Standard']);

        $this->from(route('hotel.room-types.create'))
            ->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.room-types.store'), ['name' => 'Standard'])
            ->assertRedirect(route('hotel.room-types.create'))
            ->assertSessionHasErrors([
                'name' => 'You already have a room type with this name.',
            ]);

        $this->assertDatabaseCount('hotel_room_types', 1);
    }

    public function test_hotel_cannot_open_or_delete_another_hotels_room_type(): void
    {
        $hotel = $this->createHotel();
        $otherHotel = $this->createHotel('Other Hotel', 'other.manager');
        $otherRoomType = $otherHotel->roomTypes()->create(['name' => 'Other Standard']);

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.room-types.edit', $otherRoomType))
            ->assertNotFound();

        $this->withSession(['hotel_id' => $hotel->id])
            ->delete(route('hotel.room-types.destroy', $otherRoomType))
            ->assertNotFound();

        $this->assertModelExists($otherRoomType);
    }

    public function test_hotel_can_create_a_room_with_its_type_and_uploaded_details(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Luxury Suite']);

        $this->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.rooms.store'), [
                'room_type_id' => $roomType->id,
                'name' => 'Balcony Suite',
                'adult_cost' => '3750.50',
                'child_cost' => '1800.25',
                'extra_bed_cost' => '900.00',
                'images' => [UploadedFile::fake()->image('balcony.jpg')],
                'description' => 'A bright suite overlooking the garden.',
                'highlights' => "Garden view\nKing-size bed",
                'key_points' => "Non-smoking\nDaily housekeeping",
                'amenities' => "Balcony\nAir conditioning",
            ])
            ->assertRedirect(route('hotel.rooms.index'))
            ->assertSessionHas('success', 'Room created successfully.');

        $room = HotelRoom::query()->where('hotel_id', $hotel->id)->firstOrFail();

        $this->assertSame($roomType->id, $room->room_type_id);
        $this->assertSame('3750.50', $room->adult_cost);
        $this->assertSame('1800.25', $room->child_cost);
        $this->assertSame('900.00', $room->extra_bed_cost);
        $this->assertSame('A bright suite overlooking the garden.', $room->description);
        $this->assertSame("Garden view\nKing-size bed", $room->highlights);
        $this->assertSame("Non-smoking\nDaily housekeeping", $room->key_points);
        $this->assertSame("Balcony\nAir conditioning", $room->amenities);
        Storage::disk('public')->assertExists($room->images[0]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.rooms.edit', $room))
            ->assertOk()
            ->assertSee('name="adult_cost"', false)
            ->assertSee('name="child_cost"', false)
            ->assertSee('name="extra_bed_cost"', false)
            ->assertSee('value="3750.50"', false)
            ->assertSee('value="1800.25"', false)
            ->assertSee('value="900.00"', false);
    }

    public function test_hotel_cannot_assign_a_room_type_owned_by_another_hotel(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $otherHotel = $this->createHotel('Other Hotel', 'other.manager');
        $otherRoomType = $otherHotel->roomTypes()->create(['name' => 'Other Standard']);

        $this->from(route('hotel.rooms.create'))
            ->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.rooms.store'), [
                'room_type_id' => $otherRoomType->id,
                'name' => 'Unauthorized Room',
                'adult_cost' => '2500',
                'child_cost' => '1250',
                'extra_bed_cost' => '500',
                'images' => [UploadedFile::fake()->image('room.jpg')],
                'description' => 'Room description.',
            ])
            ->assertRedirect(route('hotel.rooms.create'))
            ->assertSessionHasErrors([
                'room_type_id' => 'Select one of your hotel’s room types.',
            ]);

        $this->assertDatabaseCount('hotel_rooms', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_hotel_must_provide_all_three_room_rates_when_creating_a_room(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);

        $this->from(route('hotel.rooms.create'))
            ->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.rooms.store'), [
                'room_type_id' => $roomType->id,
                'name' => 'Standard Room',
                'images' => [UploadedFile::fake()->image('room.jpg')],
                'description' => 'A comfortable standard room.',
            ])
            ->assertRedirect(route('hotel.rooms.create'))
            ->assertSessionHasErrors(['adult_cost', 'child_cost', 'extra_bed_cost']);

        $this->assertDatabaseCount('hotel_rooms', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_hotel_can_set_zero_child_and_extra_bed_rates(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);

        $this->from(route('hotel.rooms.create'))
            ->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.rooms.store'), [
                'room_type_id' => $roomType->id,
                'name' => 'Standard Room',
                'adult_cost' => '2500',
                'child_cost' => '0',
                'extra_bed_cost' => '0',
                'images' => [UploadedFile::fake()->image('room.jpg')],
                'description' => 'A comfortable standard room.',
            ])
            ->assertRedirect(route('hotel.rooms.index'))
            ->assertSessionHas('success', 'Room created successfully.');

        $room = HotelRoom::query()->where('hotel_id', $hotel->id)->firstOrFail();
        $this->assertSame('2500.00', $room->adult_cost);
        $this->assertSame('0.00', $room->child_cost);
        $this->assertSame('0.00', $room->extra_bed_cost);
    }

    public function test_hotel_cannot_create_a_room_with_a_negative_rate(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);

        $this->from(route('hotel.rooms.create'))
            ->withSession(['hotel_id' => $hotel->id])
            ->post(route('hotel.rooms.store'), [
                'room_type_id' => $roomType->id,
                'name' => 'Standard Room',
                'adult_cost' => '-1',
                'child_cost' => '0',
                'extra_bed_cost' => '0',
                'images' => [UploadedFile::fake()->image('room.jpg')],
                'description' => 'A comfortable standard room.',
            ])
            ->assertRedirect(route('hotel.rooms.create'))
            ->assertSessionHasErrors('adult_cost');

        $this->assertDatabaseCount('hotel_rooms', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_hotel_cannot_open_or_delete_another_hotels_room(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $otherHotel = $this->createHotel('Other Hotel', 'other.manager');
        $roomType = $otherHotel->roomTypes()->create(['name' => 'Other Standard']);
        $image = UploadedFile::fake()->image('room.jpg');
        $imagePath = $image->store("hotel-rooms/{$otherHotel->id}", 'public');
        $otherRoom = $otherHotel->rooms()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Other Hotel Room',
            'images' => [$imagePath],
            'description' => 'Private room details.',
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.rooms.edit', $otherRoom))
            ->assertNotFound();

        $this->withSession(['hotel_id' => $hotel->id])
            ->delete(route('hotel.rooms.destroy', $otherRoom))
            ->assertNotFound();

        $this->assertModelExists($otherRoom);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_deleting_a_room_removes_its_uploaded_images(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);
        $imagePath = UploadedFile::fake()->image('room.jpg')->store("hotel-rooms/{$hotel->id}", 'public');
        $room = $hotel->rooms()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'images' => [$imagePath],
            'description' => 'A comfortable standard room.',
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->delete(route('hotel.rooms.destroy', $room))
            ->assertRedirect(route('hotel.rooms.index'))
            ->assertSessionHas('success', 'Room deleted successfully.');

        $this->assertDatabaseMissing('hotel_rooms', ['id' => $room->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_update_removes_only_the_room_images_selected_by_its_hotel(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);
        $firstImagePath = UploadedFile::fake()->image('first.jpg')->store("hotel-rooms/{$hotel->id}", 'public');
        $secondImagePath = UploadedFile::fake()->image('second.jpg')->store("hotel-rooms/{$hotel->id}", 'public');
        $room = $hotel->rooms()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'images' => [$firstImagePath, $secondImagePath],
            'description' => 'A comfortable standard room.',
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.rooms.update', $room), [
                'room_type_id' => $roomType->id,
                'name' => 'Standard Room',
                'adult_cost' => '2200.75',
                'child_cost' => '1100.25',
                'extra_bed_cost' => '400.00',
                'remove_images' => [$firstImagePath],
                'description' => 'A comfortable standard room.',
            ])
            ->assertRedirect(route('hotel.rooms.index'))
            ->assertSessionHas('success', 'Room updated successfully.');

        $room->refresh();
        $this->assertSame('2200.75', $room->adult_cost);
        $this->assertSame('1100.25', $room->child_cost);
        $this->assertSame('400.00', $room->extra_bed_cost);
        $this->assertSame([$secondImagePath], $room->images);
        Storage::disk('public')->assertMissing($firstImagePath);
        Storage::disk('public')->assertExists($secondImagePath);
    }

    public function test_room_type_cannot_be_deleted_while_rooms_use_it(): void
    {
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);
        $hotel->rooms()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'images' => ['hotel-rooms/'.$hotel->id.'/room.jpg'],
            'description' => 'A comfortable standard room.',
        ]);

        $this->from(route('hotel.room-types.index'))
            ->withSession(['hotel_id' => $hotel->id])
            ->delete(route('hotel.room-types.destroy', $roomType))
            ->assertRedirect(route('hotel.room-types.index'))
            ->assertSessionHasErrors([
                'room_type' => 'This room type is assigned to rooms. Reassign or delete those rooms before deleting the type.',
            ]);

        $this->assertModelExists($roomType);
    }

    public function test_room_edit_form_escapes_hotel_provided_content(): void
    {
        $hotel = $this->createHotel();
        $roomType = $hotel->roomTypes()->create(['name' => 'Standard']);
        $room = $hotel->rooms()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Standard Room',
            'images' => ['hotel-rooms/'.$hotel->id.'/room.jpg'],
            'description' => '<script>alert("room")</script>',
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.rooms.edit', $room))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;room&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("room")</script>', false);
    }

    private function createHotel(
        string $name = 'Jaipur Heritage Hotel',
        string $username = 'jaipur.manager',
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

        $hotel->hotel_code = 'HTL-'.str_pad((string) $hotel->id, 6, '0', STR_PAD_LEFT);
        $hotel->save();

        return $hotel;
    }
}
