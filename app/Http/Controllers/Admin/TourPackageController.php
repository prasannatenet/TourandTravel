<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\PackageDestination;
use App\Models\PackageInclusion;
use App\Models\PackageInclusionPackage;
use App\Models\State;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TourPackageController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'departure_city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $search = $validated['search'] ?? null;
        $departureCity = $validated['departure_city'] ?? null;
        $isActive = $validated['is_active'] ?? null;

        $query = TourPackage::query()
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($departureCity, fn ($q) => $q->where('departure_city', 'like', '%'.$departureCity.'%'));

        if (is_bool($isActive)) {
            $query->where('is_active', $isActive);
        }

        $packages = $query->with(['destinations', 'inclusionItems.inclusion', 'galleryImages', 'availabilities'])->latest('id')->paginate(15)->withQueryString();

        $countries = Country::orderBy('name')->get();
        $states = State::with('country')->orderBy('name')->get();
        $cities = City::with('state')->orderBy('name')->get();

        $hasFilter = $search || $departureCity || is_bool($isActive);

        return view('admin.packages.index', compact('packages', 'countries', 'states', 'cities', 'search', 'departureCity', 'isActive', 'hasFilter'));
    }

    public function create(): View
    {
        return view('admin.packages.create', [
            'countries' => Country::orderBy('name')->get(),
            'states' => State::with('country')->orderBy('name')->get(),
            'cities' => City::with('state')->orderBy('name')->get(),
            'places' => CityPlace::with(['city.state.country'])->orderBy('name')->get(),
            'inclusions' => PackageInclusion::orderBy('name')->get(),
            'hotels' => Hotel::with('city')->orderBy('name')->get(),
            'hotelRooms' => HotelRoom::query()->with(['hotel.city', 'roomType'])->orderBy('name')->get(),
            'inclusionItems' => [],
            'planItems' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPackageData($request);

        $image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('packages/primary', 'public');
        }

        $package = TourPackage::create(array_merge(
            Arr::only($validated, ['name', 'category', 'tour_type', 'description', 'departure_city', 'base_price', 'child_price', 'infant_price', 'discount_percentage', 'tax_percentage', 'days', 'nights']),
            ['image' => $image, 'is_active' => $request->has('is_active')]
        ));

        $this->syncGalleryImages($package, $request->file('gallery_images', []), []);
        $this->syncAvailabilities($package, $validated['availabilities'] ?? []);
        $this->syncDestinations($package, $validated['destinations'] ?? []);
        $inclusionItemIds = $this->syncInclusions($package, $validated['inclusions'] ?? []);
        $this->syncPlanItems($package, $validated['plan_items'] ?? [], $inclusionItemIds);

        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }

    public function show(TourPackage $package): View
    {
        $package->load(['destinations.destination', 'galleryImages', 'availabilities', 'inclusionItems.inclusion', 'planItems.place.city', 'planItems.packageInclusionItem.inclusion', 'planItems.hotelRoom.hotel.city', 'planItems.hotelRoom.roomType']);
        $inclusionDetails = $package->inclusionItems->mapWithKeys(function (PackageInclusionPackage $item): array {
            $inclusion = $item->inclusion;
            $hasCustomization = $item->customized;

            return [$item->id => [
                'name' => $hasCustomization ? $item->custom_name : $inclusion->name,
                'place_from' => $hasCustomization ? $item->custom_place_from : null,
                'place_to' => $hasCustomization ? $item->custom_place_to : null,
                'description' => $hasCustomization ? $item->custom_description : $inclusion->description,
                'duration_hours' => $hasCustomization ? $item->custom_duration_hours : null,
                'image_one' => $hasCustomization && $item->custom_image_one ? $item->custom_image_one : $inclusion->image_one,
                'image_two' => $hasCustomization && $item->custom_image_two ? $item->custom_image_two : $inclusion->image_two,
            ]];
        });

        $subtotal = (float) $package->base_price + $package->hotelCostPerAdult() + $package->planItems->sum(function ($item): float {
            return (float) ($item->place?->price_per_person ?? 0) + (float) $item->additional_price;
        });
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));
        $estimatedTotal = $discountedTotal * (1 + ((float) $package->tax_percentage / 100));

        return view('admin.packages.show', compact('package', 'inclusionDetails', 'estimatedTotal'));
    }

    public function edit(TourPackage $package): View
    {
        $package->load(['destinations', 'galleryImages', 'availabilities', 'inclusionItems.inclusion', 'planItems.packageInclusionItem.inclusion', 'planItems.hotelRoom.hotel.city', 'planItems.hotelRoom.roomType']);

        return view('admin.packages.edit', [
            'package' => $package,
            'countries' => Country::orderBy('name')->get(),
            'states' => State::with('country')->orderBy('name')->get(),
            'cities' => City::with('state')->orderBy('name')->get(),
            'places' => CityPlace::with(['city.state.country'])->orderBy('name')->get(),
            'inclusions' => PackageInclusion::orderBy('name')->get(),
            'hotels' => Hotel::with('city')->orderBy('name')->get(),
            'hotelRooms' => HotelRoom::query()->with(['hotel.city', 'roomType'])->orderBy('name')->get(),
            'inclusionItems' => $package->inclusionItems,
            'planItems' => $package->planItems,
        ]);
    }

    public function update(Request $request, TourPackage $package): RedirectResponse
    {
        $validated = $this->validatedPackageData($request, $package);

        $image = $package->image;
        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $image = $request->file('image')->store('packages/primary', 'public');
        }

        $package->update(array_merge(
            Arr::only($validated, ['name', 'category', 'tour_type', 'description', 'departure_city', 'base_price', 'child_price', 'infant_price', 'discount_percentage', 'tax_percentage', 'days', 'nights']),
            ['image' => $image, 'is_active' => $request->has('is_active')]
        ));

        $this->syncGalleryImages($package, $request->file('gallery_images', []), $validated['remove_gallery_images'] ?? []);
        $this->syncAvailabilities($package, $validated['availabilities'] ?? []);
        $this->syncDestinations($package, $validated['destinations'] ?? []);
        $inclusionItemIds = $this->syncInclusions($package, $validated['inclusions'] ?? []);
        $this->syncPlanItems($package, $validated['plan_items'] ?? [], $inclusionItemIds);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(TourPackage $package): RedirectResponse
    {
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }
        foreach ($package->galleryImages as $galleryImage) {
            Storage::disk('public')->delete($galleryImage->image);
        }

        PackageDestination::where('package_id', $package->id)->delete();

        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }

    private function destinationClass(string $type): string
    {
        return match ($type) {
            'country' => Country::class,
            'state' => State::class,
            'city' => City::class,
            'place' => CityPlace::class,
            default => throw new \InvalidArgumentException('Unknown destination type: '.$type),
        };
    }

    private function syncDestinations(TourPackage $package, array $groups): void
    {
        PackageDestination::where('package_id', $package->id)->delete();

        $position = 1;
        foreach ($groups as $type => $ids) {
            foreach ((array) $ids as $id) {
                PackageDestination::create([
                    'package_id' => $package->id,
                    'destination_type' => $this->destinationClass($type),
                    'destination_id' => $id,
                    'position' => $position++,
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPackageData(Request $request, ?TourPackage $package = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100', 'unique:tour_packages,name'.($package ? ','.$package->id : '')],
            'category' => ['nullable', 'string', 'max:80'],
            'tour_type' => ['required', 'in:group,private'],
            'description' => ['nullable', 'string', 'max:2000'],
            'departure_city' => ['required', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'child_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'infant_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'gallery_images' => ['nullable', 'array', 'max:8'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_gallery_images' => ['nullable', 'array', 'max:8'],
            'remove_gallery_images.*' => ['integer', 'distinct'],
            'availabilities' => ['nullable', 'array', 'max:100'],
            'availabilities.*' => ['array:id,departure_date,seat_limit,adult_price_override'],
            'availabilities.*.id' => ['nullable', 'integer'],
            'availabilities.*.departure_date' => ['required', 'date', 'distinct'],
            'availabilities.*.seat_limit' => ['required', 'integer', 'min:1', 'max:10000'],
            'availabilities.*.adult_price_override' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'days' => ['required', 'integer', 'min:1', 'max:99'],
            'nights' => ['required', 'integer', 'min:1', 'max:99'],
            'destinations' => ['nullable', 'array'],
            'destinations.country' => ['nullable', 'array'],
            'destinations.country.*' => ['integer', 'exists:countries,id'],
            'destinations.state' => ['nullable', 'array'],
            'destinations.state.*' => ['integer', 'exists:states,id'],
            'destinations.city' => ['nullable', 'array'],
            'destinations.city.*' => ['integer', 'exists:cities,id'],
            'destinations.place' => ['nullable', 'array'],
            'destinations.place.*' => ['integer', 'exists:city_places,id'],
            'inclusions' => ['nullable', 'array', 'max:100'],
            'inclusions.*' => ['array:id,package_inclusion_id,custom_name,custom_scheduled_time,custom_place_from,custom_place_to,custom_description,custom_duration_hours,custom_image_one,custom_image_two,remove_custom_image_one,remove_custom_image_two'],
            'inclusions.*.id' => ['nullable', 'integer'],
            'inclusions.*.package_inclusion_id' => ['required', 'integer', 'exists:package_inclusions,id'],
            'inclusions.*.custom_name' => ['required', 'string', 'max:120'],
            'inclusions.*.custom_scheduled_time' => ['nullable', 'date_format:H:i'],
            'inclusions.*.custom_place_from' => ['nullable', 'string', 'max:150'],
            'inclusions.*.custom_place_to' => ['nullable', 'string', 'max:150'],
            'inclusions.*.custom_description' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && preg_match_all('/\\S+/u', trim($value)) > 200) {
                    $fail('The description may not exceed 200 words.');
                }
            }],
            'inclusions.*.custom_duration_hours' => ['nullable', 'numeric', 'min:0.25', 'max:72'],
            'inclusions.*.custom_image_one' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'inclusions.*.custom_image_two' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'inclusions.*.remove_custom_image_one' => ['sometimes', 'boolean'],
            'inclusions.*.remove_custom_image_two' => ['sometimes', 'boolean'],
            'plan_items' => ['nullable', 'array', 'max:100'],
            'plan_items.*' => ['array:kind,day_number,start_time,city_place_id,hotel_room_id,package_inclusion_item_key,additional_price,show_charge_button'],
            'plan_items.*.kind' => ['required', 'string', 'in:place,inclusion,hotel'],
            'plan_items.*.day_number' => ['required', 'integer', 'min:1', 'max:99'],
            'plan_items.*.start_time' => ['required', 'date_format:H:i'],
            'plan_items.*.city_place_id' => ['nullable', 'integer', 'exists:city_places,id'],
            'plan_items.*.hotel_room_id' => ['nullable', 'integer', 'exists:hotel_rooms,id'],
            'plan_items.*.package_inclusion_item_key' => ['nullable', 'string'],
            'plan_items.*.additional_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'plan_items.*.show_charge_button' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($package, $request): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $validated = $validator->validated();
            $existingAvailabilityIds = $package?->availabilities()->pluck('id')->map(fn ($id): int => (int) $id)->all() ?? [];
            foreach ($validated['availabilities'] ?? [] as $index => $availability) {
                if (! empty($availability['id']) && ! in_array((int) $availability['id'], $existingAvailabilityIds, true)) {
                    $validator->errors()->add("availabilities.{$index}.id", 'This departure date does not belong to this package.');
                }
            }

            $existingGalleryIds = $package?->galleryImages()->pluck('id')->map(fn ($id): int => (int) $id)->all() ?? [];
            foreach ($validated['remove_gallery_images'] ?? [] as $index => $galleryImageId) {
                if (! in_array((int) $galleryImageId, $existingGalleryIds, true)) {
                    $validator->errors()->add("remove_gallery_images.{$index}", 'This image does not belong to this package.');
                }
            }

            $remainingGalleryCount = count($existingGalleryIds) - count($validated['remove_gallery_images'] ?? []);
            if ($remainingGalleryCount + count($request->file('gallery_images', [])) > 8) {
                $validator->errors()->add('gallery_images', 'A package can have up to 8 gallery images. Remove an image before adding more.');
            }
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $currentItems = $package?->inclusionItems()->get()->keyBy('id') ?? collect();
            $selectedKeys = [];
            foreach ($validated['inclusions'] ?? [] as $key => $item) {
                $selectedKeys[] = (string) $key;

                if (! empty($item['id']) && ! $currentItems->has((int) $item['id'])) {
                    $validator->errors()->add("inclusions.{$key}.id", 'This package inclusion entry does not belong to this package.');
                }
            }

            $destinationCityIds = $validated['destinations']['city'] ?? [];
            $selectedCityIds = is_array($destinationCityIds) ? array_map('intval', $destinationCityIds) : [];
            $placeIds = [];
            $hotelRoomIds = [];

            foreach ($validated['plan_items'] ?? [] as $index => $planItem) {
                if ($planItem['day_number'] > $validated['days']) {
                    $validator->errors()->add("plan_items.{$index}.day_number", 'The plan day cannot be greater than the package duration.');
                }

                if ($planItem['kind'] === 'hotel' && ! empty($planItem['hotel_room_id'])) {
                    $hotelRoomIds[] = (int) $planItem['hotel_room_id'];
                } elseif ($planItem['kind'] === 'hotel') {
                    $validator->errors()->add("plan_items.{$index}.hotel_room_id", 'Choose a hotel room for this check-in.');
                } elseif ($planItem['kind'] === 'place' && ! empty($planItem['city_place_id']) && empty($planItem['package_inclusion_id'])) {
                    $placeIds[] = (int) $planItem['city_place_id'];
                } elseif ($planItem['kind'] === 'place') {
                    $validator->errors()->add("plan_items.{$index}.city_place_id", 'Choose a place for this plan item.');
                } elseif (! empty($planItem['package_inclusion_item_key']) && empty($planItem['city_place_id'])) {
                    if (! in_array((string) $planItem['package_inclusion_item_key'], $selectedKeys, true)) {
                        $validator->errors()->add("plan_items.{$index}.package_inclusion_item_key", 'Select this package inclusion entry before adding it to the schedule.');
                    }
                } else {
                    $validator->errors()->add("plan_items.{$index}.package_inclusion_item_key", 'Choose a selected package inclusion entry for this plan item.');
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($placeIds === [] && $hotelRoomIds === []) {
                return;
            }

            $validPlaceIds = CityPlace::query()
                ->whereKey($placeIds)
                ->whereIn('city_id', $selectedCityIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($validated['plan_items'] as $index => $planItem) {
                if (($planItem['kind'] ?? null) === 'place' && ! empty($planItem['city_place_id']) && ! in_array((int) $planItem['city_place_id'], $validPlaceIds, true)) {
                    $validator->errors()->add("plan_items.{$index}.city_place_id", 'Choose a place from one of the selected cities.');
                }
            }

            if ($hotelRoomIds !== []) {
                $validHotelRoomIds = HotelRoom::query()
                    ->whereKey($hotelRoomIds)
                    ->whereNotNull('customer_adult_price')
                    ->whereNotNull('customer_child_price')
                    ->whereNotNull('customer_extra_bed_price')
                    ->whereHas('hotel', fn ($query) => $query->whereIn('city_id', $selectedCityIds))
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all();

                foreach ($validated['plan_items'] as $index => $planItem) {
                    if (($planItem['kind'] ?? null) === 'hotel' && ! empty($planItem['hotel_room_id']) && ! in_array((int) $planItem['hotel_room_id'], $validHotelRoomIds, true)) {
                        $validator->errors()->add("plan_items.{$index}.hotel_room_id", 'Choose a room with customer rates from one of the selected destination cities.');
                    }
                }
            }
        });

        return $validator->validate();
    }

    /**
     * @param  array<int, UploadedFile>  $uploads
     * @param  array<int, int|string>  $removeIds
     */
    private function syncGalleryImages(TourPackage $package, array $uploads, array $removeIds): void
    {
        foreach ($package->galleryImages()->whereIn('id', $removeIds)->get() as $galleryImage) {
            Storage::disk('public')->delete($galleryImage->image);
            $galleryImage->delete();
        }

        $position = (int) $package->galleryImages()->max('position');
        foreach ($uploads as $upload) {
            $package->galleryImages()->create([
                'image' => $upload->store("packages/{$package->id}/gallery", 'public'),
                'position' => ++$position,
            ]);
        }
    }

    /**
     * @param  array<int, array{id?: int|string|null, departure_date: string, seat_limit: int|string, adult_price_override?: float|string|null}>  $availabilities
     */
    private function syncAvailabilities(TourPackage $package, array $availabilities): void
    {
        $package->availabilities()->delete();
        foreach ($availabilities as $details) {
            $package->availabilities()->create([
                'departure_date' => $details['departure_date'],
                'seat_limit' => $details['seat_limit'],
                'adult_price_override' => $details['adult_price_override'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, int>  $inclusionItemIds
     */
    private function syncPlanItems(TourPackage $package, array $items, array $inclusionItemIds): void
    {
        $package->planItems()->delete();

        foreach ($items as $position => $item) {
            $kind = $item['kind'] ?? 'place';
            $isInclusion = $kind === 'inclusion';
            $isHotel = $kind === 'hotel';
            $package->planItems()->create([
                'city_place_id' => $kind === 'place' ? $item['city_place_id'] : null,
                'hotel_room_id' => $isHotel ? $item['hotel_room_id'] : null,
                'package_inclusion_id' => null,
                'package_inclusion_package_id' => $isInclusion ? $inclusionItemIds[$item['package_inclusion_item_key']] : null,
                'item_type' => $kind,
                'day_number' => $item['day_number'],
                'start_time' => $item['start_time'],
                'additional_price' => $isInclusion || $isHotel ? 0 : ($item['additional_price'] ?? 0),
                'show_charge_button' => $isInclusion || $isHotel ? false : (bool) ($item['show_charge_button'] ?? false),
                'position' => $position + 1,
            ]);
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     * @return array<string, int>
     */
    private function syncInclusions(TourPackage $package, array $items): array
    {
        $currentItems = $package->inclusionItems()->get()->keyBy('id');
        $inclusions = PackageInclusion::query()
            ->whereKey(collect($items)->pluck('package_inclusion_id')->unique())
            ->get()
            ->keyBy('id');
        $savedItemIds = [];
        $inclusionItemIds = [];

        foreach ($items as $key => $details) {
            $inclusion = $inclusions->get($details['package_inclusion_id']);
            $existingItem = isset($details['id']) ? $currentItems->get((int) $details['id']) : null;
            $attributes = [
                'package_inclusion_id' => $inclusion->id,
                'quantity' => 1,
                'customized' => true,
                'custom_name' => $details['custom_name'],
                'custom_place_from' => $details['custom_place_from'] ?? null,
                'custom_place_to' => $details['custom_place_to'] ?? null,
                'custom_description' => $details['custom_description'] ?? null,
                'custom_scheduled_time' => $details['custom_scheduled_time'] ?? null,
                'custom_duration_hours' => $details['custom_duration_hours'] ?? null,
            ];

            foreach (['custom_image_one', 'custom_image_two'] as $imageField) {
                $existingImage = $existingItem?->{$imageField};
                $imageFile = $details[$imageField] ?? null;
                $removeImage = (bool) ($details['remove_'.$imageField] ?? false);

                if ($imageFile) {
                    if ($existingImage) {
                        Storage::disk('public')->delete($existingImage);
                    }

                    $attributes[$imageField] = $imageFile->store("package-inclusions/{$package->id}", 'public');
                } elseif ($removeImage) {
                    if ($existingImage) {
                        Storage::disk('public')->delete($existingImage);
                    }

                    $attributes[$imageField] = null;
                } else {
                    $attributes[$imageField] = $existingImage;
                }
            }

            if ($existingItem) {
                $existingItem->update($attributes);
                $item = $existingItem;
            } else {
                $item = $package->inclusionItems()->create($attributes);
            }

            $savedItemIds[] = $item->id;
            $inclusionItemIds[(string) $key] = $item->id;
        }

        foreach ($currentItems as $currentItem) {
            if (in_array($currentItem->id, $savedItemIds, true)) {
                continue;
            }

            foreach (['custom_image_one', 'custom_image_two'] as $imageField) {
                if ($currentItem->{$imageField}) {
                    Storage::disk('public')->delete($currentItem->{$imageField});
                }
            }

            $currentItem->delete();
        }

        return $inclusionItemIds;
    }
}
