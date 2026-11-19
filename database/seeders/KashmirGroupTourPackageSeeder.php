<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\PackageDestination;
use App\Models\PackagePlanItem;
use App\Models\State;
use App\Models\TourPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KashmirGroupTourPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $india = Country::updateOrCreate(['name' => 'India'], ['name' => 'India']);
        $jammuAndKashmir = State::updateOrCreate(
            ['country_id' => $india->id, 'name' => 'Jammu and Kashmir'],
            ['name' => 'Jammu and Kashmir'],
        );

        $cityDescriptions = [
            'Srinagar' => 'The summer capital of Jammu and Kashmir, known for Dal Lake and Mughal gardens.',
            'Sonamarg' => 'A mountain valley known as the Meadow of Gold.',
            'Gulmarg' => 'A scenic Himalayan meadow known for mountain views and the Gulmarg Gondola.',
            'Pahalgam' => 'A scenic valley surrounded by pine forests and mountain rivers.',
        ];
        $cities = [];

        foreach ($cityDescriptions as $cityName => $description) {
            $cities[$cityName] = City::updateOrCreate(
                ['state_id' => $jammuAndKashmir->id, 'name' => $cityName],
                [
                    'slug' => Str::slug($cityName),
                    'primary_image' => 'destinations/primary/x.jpg',
                    'secondary_images' => [],
                    'short_description' => $description,
                ],
            );
        }

        $placeDefinitions = [
            ['city' => 'Srinagar', 'name' => 'Dal Lake', 'description' => 'Enjoy the peaceful atmosphere of Dal Lake, one of Srinagar’s best-known landmarks.'],
            ['city' => 'Srinagar', 'name' => 'Nishat Bagh', 'description' => 'Explore Nishat Bagh, a celebrated Mughal garden beside Dal Lake.'],
            ['city' => 'Srinagar', 'name' => 'Shalimar Bagh', 'description' => 'Visit Shalimar Bagh, a historic Mughal garden in Srinagar.'],
            ['city' => 'Sonamarg', 'name' => 'Sonamarg Meadow', 'description' => 'Explore the Meadow of Gold, with mountain, river and valley scenery.'],
            ['city' => 'Gulmarg', 'name' => 'Gulmarg Meadow', 'description' => 'Discover Gulmarg’s meadows, mountain viewpoints and seasonal scenery.'],
            ['city' => 'Gulmarg', 'name' => 'Gulmarg Gondola', 'description' => 'Optional Gondola ride available at an additional cost.'],
            ['city' => 'Pahalgam', 'name' => 'Betaab Valley', 'description' => 'Visit Betaab Valley, subject to accessibility and local transportation arrangements.'],
            ['city' => 'Pahalgam', 'name' => 'Aru Valley', 'description' => 'Visit Aru Valley, subject to accessibility and local transportation arrangements.'],
            ['city' => 'Pahalgam', 'name' => 'Chandanwari', 'description' => 'Visit Chandanwari, subject to accessibility and local transportation arrangements.'],
        ];
        $places = [];

        foreach ($placeDefinitions as $definition) {
            $city = $cities[$definition['city']];
            $place = CityPlace::updateOrCreate(
                ['city_id' => $city->id, 'name' => $definition['name']],
                [
                    'slug' => CityPlace::uniqueSlug($definition['name']),
                    'place_description' => Str::limit($definition['description'], 500),
                    'description' => $definition['description'],
                    'primary_image' => 'destinations/primary/x.jpg',
                    'secondary_images' => [],
                    'visit_duration_hours' => 1,
                    'best_time_to_visit' => 'morning',
                    'price_per_person' => 0,
                    'price_child' => 0,
                    'price_indian' => 0,
                    'price_foreigner' => 0,
                    'price_student' => 0,
                    'note' => $definition['name'] === 'Gulmarg Gondola'
                        ? 'Optional activity at an additional cost.'
                        : 'Sightseeing is subject to local conditions and accessibility.',
                ],
            );
            $places[$definition['name']] = $place;
        }

        $package = TourPackage::updateOrCreate(
            ['slug' => 'kashmir-group-tour-package'],
            [
                'name' => 'Kashmir Group Tour Package',
                'category' => 'Group Tour',
                'tour_type' => 'group',
                'description' => 'A five-day Kashmir group tour covering Srinagar, Sonamarg, Gulmarg and Pahalgam. Explore scenic valleys, gardens and lakes with Voyanta Services.',
                'departure_city' => 'Srinagar',
                'base_price' => 0,
                'child_price' => null,
                'infant_price' => null,
                'discount_percentage' => 0,
                'tax_percentage' => 0,
                'days' => 5,
                'nights' => 4,
                'is_active' => true,
                'image' => null,
            ],
        );

        $destinations = collect($places)->unique('id')->values();
        PackageDestination::query()
            ->where('package_id', $package->id)
            ->where('destination_type', CityPlace::class)
            ->delete();

        foreach ($destinations as $position => $place) {
            PackageDestination::create([
                'package_id' => $package->id,
                'destination_type' => CityPlace::class,
                'destination_id' => $place->id,
                'position' => $position,
            ]);
        }

        $itinerary = [
            [
                'day' => 1,
                'title' => 'Arrival in Srinagar – Local Sightseeing',
                'description' => 'Arrive in Srinagar and meet our travel representative for your transfer to the hotel. Complete the check-in process and get ready to explore the beautiful surroundings of the city. Visit popular attractions such as Dal Lake, Nishat Bagh and Shalimar Bagh. Enjoy the peaceful atmosphere and spend quality time with your fellow group travellers. Overnight stay in Srinagar.',
                'places' => ['Dal Lake', 'Nishat Bagh', 'Shalimar Bagh'],
            ],
            [
                'day' => 2,
                'title' => 'Srinagar to Sonamarg – Group Excursion',
                'description' => 'After breakfast, proceed towards Sonamarg, popularly known as the Meadow of Gold. Enjoy a scenic drive surrounded by beautiful valleys, flowing rivers and magnificent mountain views. Explore the natural beauty of the region and enjoy optional activities during your visit. Return to Srinagar for an overnight stay.',
                'places' => ['Sonamarg Meadow'],
            ],
            [
                'day' => 3,
                'title' => 'Srinagar to Gulmarg – Scenic Exploration',
                'description' => 'After breakfast, get ready for an exciting group excursion to Gulmarg. Known for its breathtaking mountain views, beautiful meadows and seasonal snowfall, Gulmarg offers a wonderful sightseeing experience. Enjoy the scenic surroundings, explore popular viewpoints and opt for the famous Gulmarg Gondola ride at an additional cost. Return to Srinagar or stay in Gulmarg as per the selected package.',
                'places' => ['Gulmarg Meadow', 'Gulmarg Gondola'],
            ],
            [
                'day' => 4,
                'title' => 'Srinagar to Pahalgam – Valley Exploration',
                'description' => 'After breakfast, travel towards Pahalgam and enjoy the beautiful landscapes along the way. Surrounded by pine forests, flowing rivers and green valleys, Pahalgam is one of the most scenic destinations in Kashmir. Explore local attractions such as Betaab Valley, Aru Valley and Chandanwari, subject to accessibility and local transportation arrangements. Enjoy your time with the group and stay overnight in Pahalgam.',
                'places' => ['Betaab Valley', 'Aru Valley', 'Chandanwari'],
            ],
            [
                'day' => 5,
                'title' => 'Pahalgam to Srinagar – Departure',
                'description' => 'Enjoy breakfast and spend some relaxing moments in the beautiful surroundings of Pahalgam. Later, proceed towards Srinagar for your onward journey. Take back wonderful memories, beautiful photographs and unforgettable experiences from your Kashmir group tour with Voyanta Services.',
                'places' => [],
            ],
        ];

        PackagePlanItem::query()->where('tour_package_id', $package->id)->delete();

        foreach ($itinerary as $day) {
            foreach ($day['places'] as $position => $placeName) {
                $place = $places[$placeName];
                $place->description = $day['title'].'. '.$day['description'];
                $place->place_description = Str::limit($place->description, 500);
                $place->save();

                PackagePlanItem::create([
                    'tour_package_id' => $package->id,
                    'city_place_id' => $place->id,
                    'item_type' => 'place',
                    'day_number' => $day['day'],
                    'start_time' => $position === 0 ? '09:00:00' : '10:00:00',
                    'additional_price' => 0,
                    'position' => $position,
                ]);
            }
        }
    }
}
