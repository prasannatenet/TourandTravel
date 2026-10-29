<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CityPlaceSeeder extends Seeder
{
    public function run(): void
    {
        $india = Country::updateOrCreate(
            ['name' => 'India'],
            ['name' => 'India']
        );

        $uttarPradesh = State::updateOrCreate(
            ['country_id' => $india->id, 'name' => 'Uttar Pradesh'],
            ['name' => 'Uttar Pradesh']
        );

        $cities = [];
        foreach (['Agra', 'Ayodhya', 'Mathura', 'Vrindavan', 'Govardhan'] as $cityName) {
            $cityNameLower = Str::lower($cityName);
            $cities[$cityName] = City::updateOrCreate(
                ['state_id' => $uttarPradesh->id, 'name' => $cityName],
                [
                    'name' => $cityName,
                    'slug' => $cityNameLower,
                    'primary_image' => 'destinations/primary/x.jpg',
                    'secondary_images' => [],
                    'short_description' => ucfirst($cityName).' destination in Uttar Pradesh.',
                ]
            );
        }
    }

    private function place(City $city, array $data): void
    {
        CityPlace::updateOrCreate(
            ['city_id' => $city->id, 'name' => $data['name']],
            array_merge($data, ['city_id' => $city->id])
        );
    }
}
