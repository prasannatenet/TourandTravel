<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomPackageRequestUpdateMail;
use App\Models\CustomPackageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class CustomPackageRequestController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ]);
        $requests = CustomPackageRequest::query()
            ->where('status', '!=', 'draft')
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.custom-package-requests.index', compact('requests', 'validated'));
    }

    public function show(CustomPackageRequest $customPackageRequest): View
    {
        abort_unless($customPackageRequest->status !== 'draft', 404);

        $itineraryCost = collect($customPackageRequest->itinerary ?? [])->sum(function (array $day) use ($customPackageRequest): float {
            $places = collect($day['places'] ?? []);
            $stateDailyCosts = $places->unique('state_id')->sum(fn (array $place): float => (float) ($place['state_daily_price'] ?? 0));
            $placeAdmissionCosts = $places->sum(fn (array $place): float => ((float) ($place['adult_price'] ?? 0) * (int) ($customPackageRequest->booking_details['adult_count'] ?? $customPackageRequest->traveler_counts['adult_count'] ?? 1))
                + ((float) ($place['child_price'] ?? 0) * (int) ($customPackageRequest->booking_details['child_count'] ?? $customPackageRequest->traveler_counts['child_count'] ?? 0)));
            $serviceCosts = collect($day['activities'] ?? [])->where('type', 'service')->sum(fn (array $activity): float => (float) ($activity['service_price'] ?? 0));

            return $placeAdmissionCosts + ($stateDailyCosts * (int) (($customPackageRequest->booking_details['adult_count'] ?? $customPackageRequest->traveler_counts['adult_count'] ?? 1) + ($customPackageRequest->booking_details['child_count'] ?? $customPackageRequest->traveler_counts['child_count'] ?? 0))) + $serviceCosts;
        });

        return view('admin.custom-package-requests.show', compact('customPackageRequest', 'itineraryCost'));
    }

    public function update(Request $request, CustomPackageRequest $customPackageRequest): RedirectResponse
    {
        abort_unless($customPackageRequest->status !== 'draft', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'admin_response' => ['nullable', 'string', 'max:2000'],
            'admin_quote' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'admin_cost_notes' => ['nullable', 'string', 'max:2000'],
            'name' => ['required', 'string', 'max:150'],
            'departure_city' => ['required', 'string', 'max:100'],
            'days' => ['required', 'integer', 'min:1', 'max:30'],
            'nights' => ['required', 'integer', 'min:0', 'max:29'],
            'description' => ['nullable', 'string', 'max:2000'],
            'day_titles' => ['nullable', 'array', 'max:30'],
            'day_titles.*' => ['nullable', 'string', 'max:150'],
            'day_notes' => ['nullable', 'array', 'max:30'],
            'day_notes.*' => ['nullable', 'string', 'max:1000'],
            'activities' => ['nullable', 'array', 'max:30'],
            'activities.*' => ['nullable', 'array', 'max:32'],
            'activities.*.*.name' => ['required', 'string', 'max:150'],
            'activities.*.*.description' => ['nullable', 'string', 'max:1000'],
            'activities.*.*.duration_hours' => ['nullable', 'numeric', 'min:0', 'max:72'],
            'activities.*.*.adult_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'activities.*.*.child_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'activities.*.*.state_daily_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'activities.*.*.service_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        $itinerary = collect($customPackageRequest->itinerary ?? [])->values()->map(function (array $day, int $index) use ($validated): array {
            $day['title'] = $validated['day_titles'][$index] ?? $day['title'] ?? null;
            $day['description'] = $validated['day_notes'][$index] ?? $day['description'] ?? null;
            $day['activities'] = collect($day['activities'] ?? [])->values()->map(function (array $activity, int $activityIndex) use ($validated, $index): array {
                $edited = $validated['activities'][$index][$activityIndex] ?? [];
                $activity['name'] = $edited['name'] ?? $activity['name'];
                $activity['description'] = $edited['description'] ?? null;
                $activity['duration_hours'] = (float) ($edited['duration_hours'] ?? 0);
                if (($activity['type'] ?? 'place') === 'place') {
                    $activity['adult_price'] = (float) ($edited['adult_price'] ?? 0);
                    $activity['child_price'] = (float) ($edited['child_price'] ?? 0);
                    $activity['state_daily_price'] = (float) ($edited['state_daily_price'] ?? 0);
                } else {
                    $activity['service_price'] = (float) ($edited['service_price'] ?? 0);
                }

                return $activity;
            })->all();
            $editedPlaces = collect($day['activities'])
                ->where('type', 'place')
                ->keyBy('id');
            $day['places'] = collect($day['places'] ?? [])->map(function (array $place) use ($editedPlaces): array {
                $edited = $editedPlaces->get((string) $place['id']);
                if ($edited === null) {
                    return $place;
                }

                foreach (['name', 'description', 'duration_hours', 'adult_price', 'child_price', 'state_daily_price'] as $field) {
                    $place[$field] = $edited[$field] ?? $place[$field] ?? null;
                }

                return $place;
            })->all();
            $day['total_duration_hours'] = collect($day['activities'])->sum(fn (array $activity): float => (float) ($activity['duration_hours'] ?? 0));

            return $day;
        })->all();

        $customPackageRequest->update([
            'name' => $validated['name'],
            'departure_city' => $validated['departure_city'],
            'days' => $validated['days'],
            'nights' => $validated['nights'],
            'description' => $validated['description'] ?? null,
            'itinerary' => $itinerary,
            'admin_response' => $validated['admin_response'] ?? null,
            'admin_quote' => $validated['admin_quote'] ?? null,
            'admin_cost_notes' => $validated['admin_cost_notes'] ?? null,
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $recipientEmail = $customPackageRequest->booking_details['email'] ?? null;
        if (is_string($recipientEmail) && $recipientEmail !== '') {
            try {
                Mail::mailer('smtp')->to($recipientEmail)->send(new CustomPackageRequestUpdateMail($customPackageRequest->fresh()));
            } catch (TransportExceptionInterface $exception) {
                report($exception);

                return redirect()->route('admin.custom-package-requests.show', $customPackageRequest)
                    ->with('error', 'The package was updated, but the customer email could not be sent. Check the email settings and try again.');
            }
        }

        return redirect()->route('admin.custom-package-requests.show', $customPackageRequest)
            ->with('success', 'The custom package was updated and an email notification was sent to the customer.');
    }
}
