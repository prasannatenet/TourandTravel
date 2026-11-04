<?php

namespace App\Http\Controllers\CabAssociate;

use App\Http\Controllers\Controller;
use App\Models\Cab;
use App\Models\CabAssociate;
use App\Models\CabUnavailableDate;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabAvailabilityController extends Controller
{
    public function show(Request $request, int $cab): View
    {
        $cab = $this->ownedCab($request, $cab);
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $calendarStart = $month->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY);
        $calendarEnd = $month->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);
        $unavailableDates = $cab->unavailableDates()
            ->whereBetween('date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
            ->pluck('date')
            ->map(static fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $days = [];
        for ($date = $calendarStart; $date <= $calendarEnd; $date = $date->addDay()) {
            $days[] = $date;
        }

        return view('cab-associate.cabs.availability', compact('cab', 'month', 'days', 'unavailableDates'));
    }

    public function update(Request $request, int $cab): RedirectResponse
    {
        $cab = $this->ownedCab($request, $cab);
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'is_available' => ['required', 'boolean'],
        ]);

        if ($validated['is_available']) {
            CabUnavailableDate::query()
                ->where('cab_id', $cab->id)
                ->whereDate('date', $validated['date'])
                ->delete();
            $message = 'Cab marked as available.';
        } else {
            CabUnavailableDate::query()->firstOrCreate([
                'cab_id' => $cab->id,
                'date' => $validated['date'],
            ]);
            $message = 'Cab marked as unavailable.';
        }

        return redirect()
            ->route('cab-associate.cabs.availability', [
                'cab' => $cab,
                'month' => substr($validated['date'], 0, 7),
            ])
            ->with('success', $message);
    }

    private function ownedCab(Request $request, int $cab): Cab
    {
        $associate = CabAssociate::findOrFail($request->session()->get('cab_associate_id'));

        return $associate->cabs()->findOrFail($cab);
    }
}
