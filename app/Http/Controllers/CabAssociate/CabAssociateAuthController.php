<?php

namespace App\Http\Controllers\CabAssociate;

use App\Http\Controllers\Controller;
use App\Models\CabAssociate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CabAssociateAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        $associateId = $request->session()->get('cab_associate_id');
        $isAuthenticatedAssociate = is_numeric($associateId)
            && CabAssociate::whereKey($associateId)
                ->where('status', 'active')
                ->whereNotNull('password')
                ->exists();

        if ($isAuthenticatedAssociate) {
            return redirect()->route('cab-associate.dashboard');
        }

        $request->session()->forget('cab_associate_id');

        return view('cab-associate.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $associate = CabAssociate::query()
            ->where('username', trim($validated['username']))
            ->where('status', 'active')
            ->first();

        if ($associate === null || ! Hash::check($validated['password'], $associate->password)) {
            return back()
                ->withErrors(['username' => 'The username or password is incorrect, or the account is inactive.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('cab_associate_id', $associate->id);

        return redirect()->route('cab-associate.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('cab_associate_id');
        $request->session()->regenerateToken();

        return redirect()->route('cab-associate.login');
    }
}
