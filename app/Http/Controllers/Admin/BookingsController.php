<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class BookingsController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::latest('created_at')
            ->paginate(20);

        return view('admin.bookings', compact('bookings'));
    }
}
