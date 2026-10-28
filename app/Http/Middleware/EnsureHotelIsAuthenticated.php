<?php

namespace App\Http\Middleware;

use App\Models\Hotel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHotelIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hotelId = $request->session()->get('hotel_id');
        $isAuthenticatedHotel = is_numeric($hotelId)
            && Hotel::whereKey($hotelId)->whereNotNull('password')->exists();

        if (! $isAuthenticatedHotel) {
            $request->session()->forget('hotel_id');

            return redirect()->route('hotel.login');
        }

        return $next($request);
    }
}
