<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class HotelAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('hotel_id')) {
            $hotelExists = Hotel::whereKey($request->session()->get('hotel_id'))
                ->whereNotNull('password')
                ->exists();

            if ($hotelExists) {
                return redirect()->route('hotel.dashboard');
            }

            $request->session()->forget('hotel_id');
        }

        return view('hotel.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_id' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $loginId = trim($validated['login_id']);
        $hotel = Hotel::query()
            ->where(function ($query) use ($loginId): void {
                $query->where('hotel_code', strtoupper($loginId))
                    ->orWhere('username', strtolower($loginId));
            })
            ->whereNotNull('password')
            ->first();

        if ($hotel === null || ! Hash::check($validated['password'], $hotel->password)) {
            return back()
                ->withErrors(['login_id' => 'The hotel ID/username or password is incorrect.'])
                ->onlyInput('login_id');
        }

        $request->session()->regenerate();
        $request->session()->put('hotel_id', $hotel->id);

        return redirect()->route('hotel.dashboard');
    }

    public function dashboard(Request $request): View
    {
        $hotel = Hotel::with(['city.state.country'])
            ->findOrFail($request->session()->get('hotel_id'));

        return view('hotel.dashboard', compact('hotel'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('hotel_id');
        $request->session()->regenerateToken();

        return redirect()->route('hotel.login');
    }
}
