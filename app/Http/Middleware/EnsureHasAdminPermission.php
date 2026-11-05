<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if ($user === null) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($routeName === 'admin.logout') {
            return $next($request);
        }

        if ($routeName === 'admin.roles-permissions.index' || str_starts_with((string) $routeName, 'admin.roles-permissions.')) {
            abort(403, 'Only administrators can manage roles and permissions.');
        }

        $routeParts = explode('.', str_replace('admin.', '', (string) $routeName));
        $module = match ($routeParts[0] ?? '') {
            'dashboard' => 'dashboard',
            'destinations' => 'destinations',
            'countries', 'states', 'cities', 'places' => 'destinations',
            'inclusions' => 'inclusions',
            'packages' => 'packages',
            'hotels' => 'hotels',
            'bookings', 'hotel-booking-requests' => 'bookings',
            'cabs' => 'cabs',
            'cab-associates' => 'cab-associates',
            'email-settings' => 'email-settings',
            default => null,
        };

        if ($module === null) {
            abort(403);
        }

        $method = $request->method();
        $routeAction = end($routeParts);
        $action = match (true) {
            $method === 'GET' && $routeAction === 'create' => 'create',
            $method === 'GET' && $routeAction === 'process' => 'update',
            $method === 'GET' => 'view',
            $method === 'POST' && $routeAction === 'store' => 'create',
            in_array($method, ['POST', 'PUT', 'PATCH'], true) => 'update',
            $method === 'DELETE' => 'delete',
            default => null,
        };

        $permissionName = $action === null ? null : "{$module}.{$action}";
        $permissionExists = $permissionName !== null
            && config("admin_permissions.{$module}.permissions.{$action}") !== null;
        $hasPermission = $permissionExists
            && $user->getAllPermissions()->contains('name', $permissionName);

        abort_unless($hasPermission, 403, 'You do not have permission to access this page.');

        return $next($request);
    }
}
