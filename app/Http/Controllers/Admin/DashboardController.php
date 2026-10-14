<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'total_bookings' => 132,
            'pending_bookings' => 18,
            'monthly_revenue' => 48560,
        ];

        $recentBookings = [
            ['id' => 'BK-1024', 'customer' => 'Rahul Sharma', 'tour' => 'Goa Beach Escape - 3N/4D', 'date' => '2026-10-12', 'amount' => 24500, 'status' => 'Confirmed'],
            ['id' => 'BK-1023', 'customer' => 'Priya Patel', 'tour' => 'Kerala Backwaters Houseboat', 'date' => '2026-10-11', 'amount' => 32000, 'status' => 'Pending'],
            ['id' => 'BK-1022', 'customer' => 'Amit Verma', 'tour' => 'Jaipur Pink City Tour', 'date' => '2026-10-10', 'amount' => 15500, 'status' => 'Confirmed'],
            ['id' => 'BK-1021', 'customer' => 'Sneha Reddy', 'tour' => 'Manali Adventure Package', 'date' => '2026-10-09', 'amount' => 28000, 'status' => 'Cancelled'],
            ['id' => 'BK-1020', 'customer' => 'Vikram Singh', 'tour' => 'Dubai Explorer - 5N/6D', 'date' => '2026-10-08', 'amount' => 65000, 'status' => 'Confirmed'],
        ];

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
