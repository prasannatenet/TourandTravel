<?php

namespace App\Http\Controllers\CabAssociate;

use App\Http\Controllers\Controller;
use App\Models\CabAssociate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $associate = CabAssociate::with('city')
            ->withCount('cabs')
            ->findOrFail($request->session()->get('cab_associate_id'));
        $cabs = $associate->cabs()->with('city')->latest()->limit(5)->get();

        return view('cab-associate.dashboard', compact('associate', 'cabs'));
    }
}
