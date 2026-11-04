<?php

namespace App\Http\Middleware;

use App\Models\CabAssociate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCabAssociateIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $associateId = $request->session()->get('cab_associate_id');
        $isAuthenticatedAssociate = is_numeric($associateId)
            && CabAssociate::whereKey($associateId)
                ->where('status', 'active')
                ->whereNotNull('password')
                ->exists();

        if (! $isAuthenticatedAssociate) {
            $request->session()->forget('cab_associate_id');

            return redirect()->route('cab-associate.login');
        }

        return $next($request);
    }
}
