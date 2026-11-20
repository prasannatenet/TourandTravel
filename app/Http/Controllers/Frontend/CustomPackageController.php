<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\CustomPackageRequest;
use App\Models\State;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomPackageController extends Controller
{
    public function index(Request $request): View
    {
        $ownerToken = $request->session()->get('custom_package_owner_token');
        $customPackages = CustomPackageRequest::query()
            ->where('owner_token', is_string($ownerToken) ? $ownerToken : '')
            ->latest()
            ->paginate(10);

        return view('custom-packages.index', compact('customPackages'));
    }

    public function create(Request $request): View
    {
        $places = CityPlace::with('city.state.country')->orderBy('name')->get();
        $countries = Country::with(['states' => fn ($query) => $query->with(['cities' => fn ($cityQuery) => $cityQuery->withCount('places')->orderBy('name')])->orderBy('name')])->orderBy('name')->get();
        $availablePlaces = $places->map(fn (CityPlace $place): array => [
            'id' => $place->id,
            'label' => $place->name.' - '.$place->city->name.', '.$place->city->state->name,
            'adult_price' => $place->adultPriceForCountry('India'),
            'child_price' => $place->childPrice(),
            'duration_hours' => (float) ($place->visit_duration_hours ?? 0),
            'best_time' => $place->best_time_to_visit,
            'state_id' => $place->city->state->id,
            'city_id' => $place->city->id,
            'state_daily_price' => (float) $place->city->state->price_per_day,
        ])->values();
        $oldItinerary = $request->old('itinerary', [['day_number' => 1, 'title' => '', 'description' => '', 'place_ids' => [], 'inclusions' => []]]);
        $oldInclusions = $request->old('inclusions', [['name' => '', 'description' => '']]);

        return view('custom-packages.create', compact('places', 'availablePlaces', 'countries', 'oldItinerary', 'oldInclusions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'departure_city' => ['required', 'string', 'max:100'],
            'country_ids' => ['required', 'array', 'min:1'],
            'country_ids.*' => ['required', 'integer', 'distinct', 'exists:countries,id'],
            'state_ids' => ['required', 'array', 'min:1'],
            'state_ids.*' => ['required', 'integer', 'distinct', 'exists:states,id'],
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['required', 'integer', 'distinct', 'exists:cities,id'],
            'days' => ['required', 'integer', 'min:1', 'max:30'],
            'nights' => ['required', 'integer', 'min:0', 'max:29'],
            'adult_count' => ['required', 'integer', 'min:1', 'max:99'],
            'child_count' => ['required', 'integer', 'min:0', 'max:99'],
            'description' => ['nullable', 'string', 'max:2000'],
            'itinerary' => ['required', 'array', 'min:1', 'max:30'],
            'itinerary.*.day_number' => ['required', 'integer', 'min:1', 'max:30', 'distinct', function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                if ((int) $value > (int) $request->input('days', 1)) {
                    $fail('An itinerary day cannot be greater than the package duration.');
                }
            }],
            'itinerary.*.title' => ['nullable', 'string', 'max:150'],
            'itinerary.*.description' => ['nullable', 'string', 'max:1000'],
            'itinerary.*.start_time' => ['nullable', 'date_format:H:i'],
            'itinerary.*.place_ids' => ['nullable', 'array', 'max:12'],
            'itinerary.*.place_ids.*' => ['required', 'integer', 'exists:city_places,id'],
            'itinerary.*.inclusions' => ['nullable', 'array', 'max:20'],
            'itinerary.*.inclusions.*' => ['required', 'integer', 'min:0'],
            'itinerary.*.activities' => ['nullable', 'array', 'max:32'],
            'itinerary.*.activities.*' => ['required', 'regex:/^(place|service):\d+$/'],
            'inclusions' => ['nullable', 'array', 'max:20'],
            'inclusions.*.name' => ['nullable', 'string', 'max:120'],
            'inclusions.*.description' => ['nullable', 'string', 'max:1000'],
            'inclusions.*.duration_hours' => ['nullable', 'numeric', 'min:0.25', 'max:72'],
        ]);

        $stateIdsForCountries = State::query()->whereIn('country_id', $validated['country_ids'])->whereIn('id', $validated['state_ids'])->pluck('id')->map(fn (int $id): string => (string) $id)->all();
        if (count($stateIdsForCountries) !== count($validated['state_ids'])) {
            return back()->withErrors(['state_ids' => 'Choose states that belong to the selected countries.'])->withInput();
        }

        $cityIdsForStates = State::query()->whereIn('id', $validated['state_ids'])->with(['cities' => fn ($query) => $query->whereIn('id', $validated['city_ids'])])->get()->flatMap(fn (State $state) => $state->cities->pluck('id'))->map(fn (int $id): string => (string) $id)->all();
        if (count($cityIdsForStates) !== count($validated['city_ids'])) {
            return back()->withErrors(['city_ids' => 'Choose cities that belong to the selected states.'])->withInput();
        }

        $hasActivities = collect($validated['itinerary'])->contains(fn (array $day): bool => ! empty($day['place_ids'] ?? []) || ! empty($day['inclusions'] ?? []) || ! empty($day['activities'] ?? []));
        if (! $hasActivities) {
            return back()->withErrors(['itinerary' => 'Add at least one place or service stop to your itinerary.'])->withInput();
        }

        $selectedCityIds = array_map('strval', $validated['city_ids']);
        $invalidPlaceExists = collect($validated['itinerary'])->flatMap(fn (array $day): array => $day['place_ids'] ?? [])
            ->contains(fn (int|string $placeId): bool => ! CityPlace::query()->whereKey($placeId)->whereIn('city_id', $selectedCityIds)->exists());
        if ($invalidPlaceExists) {
            return back()->withErrors(['itinerary' => 'Choose places from the selected destination cities.'])->withInput();
        }

        $placeIds = collect($validated['itinerary'])->flatMap(fn (array $day): array => $day['place_ids'] ?? [])->unique()->values();
        $places = CityPlace::with('city.state.country')->whereIn('id', $placeIds)->get()->keyBy('id');
        $itinerary = collect($validated['itinerary'])->map(function (array $day) use ($places, $validated): array {
            $dayPlaces = collect($day['place_ids'] ?? [])->map(function (int|string $placeId) use ($places): array {
                $place = $places->get((int) $placeId);

                return [
                    'type' => 'place',
                    'id' => (string) $place->id,
                    'name' => $place->name,
                    'slug' => $place->slug,
                    'description' => $place->place_description,
                    'city' => $place->city->name,
                    'state' => $place->city->state->name,
                    'country' => $place->city->state->country->name,
                    'image_path' => $place->primary_image,
                    'adult_price' => $place->adultPriceForCountry('India'),
                    'child_price' => $place->childPrice(),
                    'duration_hours' => (float) ($place->visit_duration_hours ?? 0),
                    'best_time' => $place->best_time_to_visit,
                    'state_id' => $place->city->state->id,
                    'state_daily_price' => (float) $place->city->state->price_per_day,
                ];
            })->keyBy('id');
            $scheduledInclusions = collect($day['inclusions'] ?? [])->map(function (int|string $inclusionIndex) use ($validated): array {
                $inclusion = $validated['inclusions'][$inclusionIndex] ?? null;

                return [
                    'type' => 'service',
                    'id' => (string) $inclusionIndex,
                    'name' => trim((string) ($inclusion['name'] ?? '')),
                    'description' => filled($inclusion['description'] ?? null) ? trim($inclusion['description']) : null,
                    'duration_hours' => (float) ($inclusion['duration_hours'] ?? 0),
                    'index' => (int) $inclusionIndex,
                ];
            })->filter(fn (array $inclusion): bool => $inclusion['name'] !== '' && isset($validated['inclusions'][$inclusion['index']]))->values();

            $orderedActivities = collect($day['activities'] ?? array_merge(
                collect($dayPlaces->keys())->map(fn (string|int $id): string => 'place:'.$id)->all(),
                collect($scheduledInclusions)->map(fn (array $inclusion): string => 'service:'.$inclusion['index'])->all(),
            ));
            $selectedActivities = collect($dayPlaces->keys())->map(fn (string|int $id): string => 'place:'.$id)
                ->merge($scheduledInclusions->map(fn (array $inclusion): string => 'service:'.$inclusion['index']))
                ->sort()
                ->values();
            if ($orderedActivities->unique()->sort()->values()->all() !== $selectedActivities->all()) {
                return back()->withErrors(['itinerary' => 'Review the places and services in your day plan.'])->withInput();
            }

            return [
                'day_number' => (int) $day['day_number'],
                'title' => $day['title'] ?? null,
                'description' => $day['description'] ?? null,
                'start_time' => $day['start_time'] ?? null,
                'total_duration_hours' => $orderedActivities->sum(function (string $activity) use ($dayPlaces, $scheduledInclusions): float {
                    [$type, $id] = explode(':', $activity, 2);

                    return $type === 'place'
                        ? (float) ($dayPlaces->get($id)['duration_hours'] ?? 0)
                        : (float) ($scheduledInclusions->firstWhere('index', (int) $id)['duration_hours'] ?? 0);
                }),
                'inclusions' => $scheduledInclusions->all(),
                'places' => $dayPlaces->values()->all(),
                'activities' => $orderedActivities->map(function (string $activity) use ($dayPlaces, $scheduledInclusions): ?array {
                    [$type, $id] = explode(':', $activity, 2);

                    $activityData = $type === 'place'
                        ? $dayPlaces->get($id)
                        : $scheduledInclusions->firstWhere('index', (int) $id);

                    if ($activityData === null) {
                        return null;
                    }

                    unset($activityData['index']);

                    return $activityData;
                })->filter()->values()->all(),
            ];
        })->sortBy('day_number')->values()->all();
        $inclusions = collect($validated['inclusions'] ?? [])
            ->filter(fn (array $inclusion): bool => filled($inclusion['name'] ?? null))
            ->map(fn (array $inclusion): array => [
                'name' => trim($inclusion['name']),
                'description' => filled($inclusion['description'] ?? null) ? trim($inclusion['description']) : null,
                'duration_hours' => (float) ($inclusion['duration_hours'] ?? 0),
            ])
            ->values()
            ->all();
        $ownerToken = $request->session()->get('custom_package_owner_token');

        if (! is_string($ownerToken) || strlen($ownerToken) !== 64) {
            $ownerToken = Str::random(64);
            $request->session()->put('custom_package_owner_token', $ownerToken);
        }

        $customPackage = CustomPackageRequest::create([
            'owner_token' => $ownerToken,
            'name' => $validated['name'],
            'departure_city' => $validated['departure_city'],
            'days' => (int) $validated['days'],
            'nights' => (int) $validated['nights'],
            'description' => $validated['description'] ?? null,
            'itinerary' => $itinerary,
            'inclusions' => $inclusions,
            'traveler_counts' => [
                'adult_count' => (int) $validated['adult_count'],
                'child_count' => (int) $validated['child_count'],
            ],
            'status' => 'draft',
        ]);

        return redirect()->route('custom-packages.show', $customPackage)
            ->with('success', 'Your private package is saved. Add your booking details to send it to the admin.');
    }

    public function show(Request $request, CustomPackageRequest $customPackageRequest): View
    {
        $customPackage = $this->ownedPackage($request, $customPackageRequest);

        return view('custom-packages.show', compact('customPackage'));
    }

    public function bookingForm(Request $request, CustomPackageRequest $customPackageRequest): View
    {
        $customPackage = $this->ownedPackage($request, $customPackageRequest);
        abort_unless($customPackage->status === 'draft', 404);

        return view('custom-packages.booking', compact('customPackage'));
    }

    public function submitBooking(Request $request, CustomPackageRequest $customPackageRequest): RedirectResponse
    {
        $customPackage = $this->ownedPackage($request, $customPackageRequest);
        abort_unless($customPackage->status === 'draft', 404);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'departure_date' => ['nullable', 'date', 'after_or_equal:today'],
            'adult_count' => ['required', 'integer', 'min:1', 'max:99'],
            'child_count' => ['required', 'integer', 'min:0', 'max:99'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $customPackage->update([
            'booking_details' => $validated,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return redirect()->route('custom-packages.show', $customPackage)
            ->with('success', 'Your custom package and booking request have been sent to the admin for review.');
    }

    private function ownedPackage(Request $request, CustomPackageRequest $customPackageRequest): CustomPackageRequest
    {
        $ownerToken = $request->session()->get('custom_package_owner_token');
        abort_unless(is_string($ownerToken) && hash_equals($customPackageRequest->owner_token, $ownerToken), 404);

        return $customPackageRequest;
    }
}
