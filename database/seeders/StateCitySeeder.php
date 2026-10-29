<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;

/**
 * Seeds Indian states and their cities for the tour and travel application.
 *
 * The City model automatically generates a unique slug from the city name
 * through its booted callbacks, so slugs do not need to be set manually.
 *
 * @see Country
 * @see State
 * @see City
 */
class StateCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $india = Country::updateOrCreate(
            ['name' => 'India'],
            ['name' => 'India']
        );

        $states = [
            'Uttar Pradesh' => [
                'Varanasi' => 'Spiritual capital on the banks of the Ganges.',
                'Agra' => 'Home to the Taj Mahal.',
                'Ayodhya' => 'Birthplace of Lord Rama.',
                'Mathura' => 'Birthplace of Lord Krishna.',
            ],
            'Rajasthan' => [
                'Jaipur' => 'The Pink City and royal heritage.',
                'Udaipur' => 'City of lakes and palaces.',
                'Jodhpur' => 'The Blue City with Mehrangarh Fort.',
                'Jaisalmer' => 'The Golden City in the Thar Desert.',
            ],
            'Maharashtra' => [
                'Mumbai' => 'Financial capital and Bollywood hub.',
                'Pune' => 'Cultural and educational center.',
                'Shirdi' => 'Home of Sai Baba temple.',
                'Chhatrapati Sambhajinagar' => 'Historic city with Ajanta and Ellora caves.',
            ],
            'Tamil Nadu' => [
                'Chennai' => 'Gateway to South India.',
                'Madurai' => 'Temple city with Meenakshi Amman Temple.',
                'Rameswaram' => 'Pilgrimage island on Pamban Island.',
                'Ooty' => 'Queen of hill stations in the Nilgiris.',
            ],
            'Uttarakhand' => [
                'Haridwar' => 'Holy city on the Ganges.',
                'Rishikesh' => 'Yoga capital of the world.',
                'Dehradun' => 'Capital city in the Doon Valley.',
                'Nainital' => 'Lake district of Uttarakhand.',
            ],
            'Kerala' => [
                'Kochi' => 'Queen of the Arabian Sea.',
                'Thiruvananthapuram' => 'Capital city of Kerala.',
                'Munnar' => 'Hill station with tea plantations.',
                'Alappuzha' => 'Venice of the East with backwaters.',
            ],
            'Goa' => [
                'Panaji' => 'State capital on the Mandovi River.',
                'Calangute' => 'Popular beach destination.',
                'Baga' => 'Lively beach and nightlife.',
                'South Goa destinations' => 'Serene beaches and Portuguese heritage.',
            ],
        ];

        foreach ($states as $stateName => $cities) {
            $state = State::updateOrCreate(
                ['country_id' => $india->id, 'name' => $stateName],
                ['name' => $stateName]
            );

            foreach ($cities as $cityName => $description) {
                City::updateOrCreate(
                    ['state_id' => $state->id, 'name' => $cityName],
                    [
                        'name' => $cityName,
                        'primary_image' => 'x.jpg',
                        'secondary_images' => [],
                        'short_description' => $description,
                    ]
                );
            }
        }
    }
}
