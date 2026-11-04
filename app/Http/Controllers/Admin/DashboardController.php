<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();

        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('is_admin', true)->count(),
            'total_destinations' => City::count(),
            'total_countries' => Country::count(),
            'total_states' => State::count(),
            'total_tours' => TourPackage::count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'hotel_requests_pending' => Booking::where('hotel_request_status', 'requested')->count(),
            'monthly_revenue' => Booking::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('amount'),
            'total_revenue' => Booking::sum('amount'),
        ];

        $recentBookings = Booking::with('package')
            ->latest('created_at')
            ->take(5)
            ->get(['id', 'name', 'country', 'package_id', 'itinerary_override', 'status', 'amount', 'created_at'])
            ->map(function ($booking) {
                return [
                    'id' => 'BK-'.$booking->id,
                    'customer' => $booking->name,
                    'tour' => $booking->itinerary_override['custom_package']['name'] ?? $booking->package?->name ?? 'Package',
                    'date' => $booking->created_at?->format('Y-m-d') ?? '',
                    'amount' => (float) $booking->amount,
                    'status' => ucfirst($booking->status),
                ];
            })
            ->values()
            ->all();

        $bookingsByMonth = Booking::where('created_at', '>=', $sixMonthsAgo)
            ->get(['created_at'])
            ->groupBy(fn ($booking) => $booking->created_at->format('Y-m'));

        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $chartLabels[] = $now->copy()->subMonths($i)->format('M');
            $chartData[] = (int) $bookingsByMonth->get($key, collect())->count();
        }

        $revenueByMonth = Booking::where('created_at', '>=', $sixMonthsAgo)
            ->get(['created_at', 'amount'])
            ->groupBy(fn ($booking) => $booking->created_at->format('Y-m'));

        $revenueChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $revenueChartData[] = (float) $revenueByMonth->get($key, collect())->sum('amount');
        }

        $statusDistribution = Booking::whereNotNull('status')
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $topPackages = Booking::with('package')
            ->whereNotNull('package_id')
            ->get(['package_id', 'id'])
            ->groupBy('package_id')
            ->map(function ($group) {
                $package = $group->first()->package;

                return [
                    'name' => $package?->name ?? 'Unknown',
                    'bookings' => $group->count(),
                ];
            })
            ->sortByDesc('bookings')
            ->take(5)
            ->values()
            ->all();

        $recentUsers = User::latest('created_at')
            ->take(5)
            ->get(['id', 'name', 'email', 'is_admin', 'created_at']);

        return view('admin.dashboard', compact(
            'stats',
            'recentBookings',
            'chartLabels',
            'chartData',
            'revenueChartData',
            'statusDistribution',
            'topPackages',
            'recentUsers'
        ));
    }
}
