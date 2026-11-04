<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HotelImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_can_upload_one_primary_and_multiple_secondary_photos(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'primary_image' => UploadedFile::fake()->image('front.jpg'),
                'secondary_images' => [
                    UploadedFile::fake()->image('lobby.jpg'),
                    UploadedFile::fake()->image('pool.png'),
                ],
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHas('success', 'Primary and secondary hotel photos updated successfully.');

        $hotel->refresh();
        $this->assertNotNull($hotel->primary_image);
        $this->assertCount(2, $hotel->secondary_images);
        Storage::disk('public')->assertExists($hotel->primary_image);
        foreach ($hotel->secondary_images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }

        $this->withSession(['hotel_id' => $hotel->id])
            ->get(route('hotel.images.index'))
            ->assertOk()
            ->assertSee('Hotel Photos')
            ->assertSee('Primary Hotel Image')
            ->assertSee('Secondary Hotel Images')
            ->assertSee('name="primary_image"', false)
            ->assertSee('name="secondary_images[]"', false)
            ->assertSee($hotel->name);
    }

    public function test_hotel_can_remove_one_of_its_existing_photos(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $primaryImage = UploadedFile::fake()->image('front.jpg')->store("hotels/{$hotel->id}/primary", 'public');
        $imageToKeep = UploadedFile::fake()->image('lobby.jpg')->store("hotels/{$hotel->id}/secondary", 'public');
        $imageToRemove = UploadedFile::fake()->image('pool.jpg')->store("hotels/{$hotel->id}/secondary", 'public');
        $hotel->update([
            'primary_image' => $primaryImage,
            'secondary_images' => [$imageToKeep, $imageToRemove],
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'remove_secondary_images' => [$imageToRemove],
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHas('success', 'Primary and secondary hotel photos updated successfully.');

        $hotel->refresh();
        $this->assertSame($primaryImage, $hotel->primary_image);
        $this->assertSame([$imageToKeep], $hotel->secondary_images);
        Storage::disk('public')->assertExists($primaryImage);
        Storage::disk('public')->assertExists($imageToKeep);
        Storage::disk('public')->assertMissing($imageToRemove);
    }

    public function test_hotel_can_replace_or_remove_its_primary_image(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $oldPrimary = UploadedFile::fake()->image('old-front.jpg')->store("hotels/{$hotel->id}/primary", 'public');
        $secondaryImage = UploadedFile::fake()->image('lobby.jpg')->store("hotels/{$hotel->id}/secondary", 'public');
        $hotel->update([
            'primary_image' => $oldPrimary,
            'secondary_images' => [$secondaryImage],
        ]);

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'primary_image' => UploadedFile::fake()->image('new-front.jpg'),
            ])
            ->assertRedirect(route('hotel.images.index'));

        $hotel->refresh();
        Storage::disk('public')->assertMissing($oldPrimary);
        Storage::disk('public')->assertExists($hotel->primary_image);
        $this->assertSame([$secondaryImage], $hotel->secondary_images);

        $this->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), ['remove_primary_image' => '1'])
            ->assertRedirect(route('hotel.images.index'));

        $hotel->refresh();
        $this->assertNull($hotel->primary_image);
        $this->assertSame([$secondaryImage], $hotel->secondary_images);
        Storage::disk('public')->assertExists($secondaryImage);
    }

    public function test_hotel_photo_upload_is_limited_to_ten_images(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();

        $this->from(route('hotel.images.index'))
            ->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'secondary_images' => array_map(
                    fn (int $number): UploadedFile => UploadedFile::fake()->image("hotel-{$number}.jpg"),
                    range(1, 11),
                ),
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHasErrors('secondary_images');

        $this->assertNull($hotel->fresh()->primary_image);
        $this->assertNull($hotel->fresh()->secondary_images);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_hotel_photo_upload_counts_primary_and_existing_photos_toward_limit(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $primaryImage = UploadedFile::fake()->image('front.jpg')->store("hotels/{$hotel->id}/primary", 'public');
        $secondaryImages = [];
        foreach (range(1, 9) as $number) {
            $secondaryImages[] = UploadedFile::fake()->image("secondary-{$number}.jpg")
                ->store("hotels/{$hotel->id}/secondary", 'public');
        }
        $hotel->update([
            'primary_image' => $primaryImage,
            'secondary_images' => $secondaryImages,
        ]);

        $this->from(route('hotel.images.index'))
            ->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'secondary_images' => [UploadedFile::fake()->image('extra.jpg')],
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHasErrors('secondary_images');

        $hotel->refresh();
        $this->assertSame($primaryImage, $hotel->primary_image);
        $this->assertSame($secondaryImages, $hotel->secondary_images);
        $this->assertCount(10, Storage::disk('public')->allFiles());
    }

    public function test_hotel_photo_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();

        $this->from(route('hotel.images.index'))
            ->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'secondary_images' => [UploadedFile::fake()->create('document.txt', 100, 'text/plain')],
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHasErrors('secondary_images.0');

        $this->assertNull($hotel->fresh()->primary_image);
        $this->assertNull($hotel->fresh()->secondary_images);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_guest_is_redirected_from_hotel_photos(): void
    {
        $this->get(route('hotel.images.index'))
            ->assertRedirect(route('hotel.login'));

        $this->put(route('hotel.images.update'), [])
            ->assertRedirect(route('hotel.login'));
    }

    public function test_hotel_cannot_remove_another_hotels_secondary_photo(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $otherHotel = $this->createHotel('Other Hotel', 'other.manager');
        $otherImage = UploadedFile::fake()->image('other.jpg')->store("hotels/{$otherHotel->id}/secondary", 'public');
        $otherHotel->update(['secondary_images' => [$otherImage]]);

        $this->from(route('hotel.images.index'))
            ->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'remove_secondary_images' => [$otherImage],
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHasErrors('remove_secondary_images.0');

        $this->assertSame([$otherImage], $otherHotel->fresh()->secondary_images);
        Storage::disk('public')->assertExists($otherImage);
    }

    public function test_hotel_photo_removal_rejects_non_string_paths(): void
    {
        Storage::fake('public');
        $hotel = $this->createHotel();
        $secondaryImage = UploadedFile::fake()->image('lobby.jpg')->store("hotels/{$hotel->id}/secondary", 'public');
        $hotel->update(['secondary_images' => [$secondaryImage]]);

        $this->from(route('hotel.images.index'))
            ->withSession(['hotel_id' => $hotel->id])
            ->put(route('hotel.images.update'), [
                'remove_secondary_images' => [['unexpected' => 'value']],
            ])
            ->assertRedirect(route('hotel.images.index'))
            ->assertSessionHasErrors('remove_secondary_images.0');

        $this->assertSame([$secondaryImage], $hotel->fresh()->secondary_images);
        Storage::disk('public')->assertExists($secondaryImage);
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

        return Hotel::create([
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
    }
}
