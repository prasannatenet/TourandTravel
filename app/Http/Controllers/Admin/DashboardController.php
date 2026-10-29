<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('is_admin', true)->count(),
            'total_destinations' => City::count(),
            'total_countries' => Country::count(),
            'total_states' => State::count(),
            // Placeholder stats for Tour & Travel modules (wire to real models later)
            'total_tours' => 24,
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'monthly_revenue' => 48560,
        ];

        $recentBookings = Booking::latest('created_at')
            ->take(5)
            ->get(['id', 'name', 'mobile', 'country', 'status'])
            ->map(function ($booking) {
                return [
                    'id' => 'BK-'.$booking->id,
                    'customer' => $booking->name,
                    'tour' => $booking->package?->name ?? 'Package',
                    'date' => $booking->created_at->format('Y-m-d'),
                    'amount' => rand(15000, 75000),
                    'status' => ucfirst($booking->status),
                ];
            })
            ->values()
            ->all();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
