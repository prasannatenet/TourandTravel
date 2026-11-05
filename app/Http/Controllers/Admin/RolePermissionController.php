<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $guardName = config('auth.defaults.guard', 'web');
        $permissionNames = [];

        foreach (config('admin_permissions', []) as $module => $definition) {
            foreach (array_keys($definition['permissions']) as $action) {
                $permissionName = "{$module}.{$action}";
                $permissionNames[] = $permissionName;
                Permission::findOrCreate($permissionName, $guardName);
            }
        }

        $roles = Role::query()
            ->where('guard_name', $guardName)
            ->with(['permissions' => fn ($query) => $query->orderBy('name')])
            ->withCount('users')
            ->orderBy('name')
            ->get();
        $permissions = Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $permissionNames)
            ->orderBy('name')
            ->get();
        $permissionsByName = $permissions->keyBy('name');
        $permissionGroups = collect(config('admin_permissions', []))
            ->map(function (array $definition, string $module) use ($permissionsByName): array {
                return [
                    'label' => $definition['label'],
                    'permissions' => collect(array_keys($definition['permissions']))
                        ->map(fn (string $action) => $permissionsByName->get("{$module}.{$action}"))
                        ->filter()
                        ->values(),
                ];
            });

        return view('admin.role-permission', compact('roles', 'permissions', 'permissionGroups'));
    }

    public function createRole(Request $request): RedirectResponse
    {
        $guardName = config('auth.defaults.guard', 'web');
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', $guardName),
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id')->where('guard_name', $guardName),
            ],
            'account_name' => ['nullable', 'required_with:account_email,account_password', 'string', 'max:255'],
            'account_email' => [
                'nullable',
                'required_with:account_name,account_password',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'account_password' => [
                'nullable',
                'required_with:account_name,account_email',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $role = DB::transaction(function () use ($validated, $guardName): Role {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => $guardName,
            ]);

            $role->syncPermissions($this->selectedPermissions($validated['permissions'] ?? [], $guardName));

            if (! empty($validated['account_email'])) {
                $user = User::create([
                    'name' => $validated['account_name'],
                    'email' => $validated['account_email'],
                    'password' => $validated['account_password'],
                    'is_admin' => false,
                ]);
                $user->assignRole($role);
            }

            return $role;
        });

        $response = redirect()
            ->route('admin.roles-permissions.index')
            ->with('success', 'Role created successfully.');

        if (! empty($validated['account_email'])) {
            $response->with('created_account', [
                'name' => $validated['account_name'],
                'email' => $validated['account_email'],
            ]);
        }

        return $response;
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $guardName = config('auth.defaults.guard', 'web');
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $guardName)
                    ->ignore($id),
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id')->where('guard_name', $guardName),
            ],
        ]);

        $role = Role::query()
            ->where('guard_name', $guardName)
            ->findOrFail($id);
        $role->name = $validated['name'];
        $role->save();
        $role->syncPermissions($this->selectedPermissions($validated['permissions'] ?? [], $guardName));

        return redirect()
            ->route('admin.roles-permissions.index')
            ->with('success', 'Role updated successfully.');
    }

    public function deleteRole(int $id): RedirectResponse
    {
        $role = Role::query()
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->findOrFail($id);
        $role->delete();

        return redirect()
            ->route('admin.roles-permissions.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function assignRoleToUser(Request $request, int $userId): RedirectResponse
    {
        $guardName = config('auth.defaults.guard', 'web');
        $validated = $request->validate([
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where('guard_name', $guardName),
            ],
        ]);

        $user = User::findOrFail($userId);
        $user->assignRole(Role::findById($validated['role_id'], $guardName));

        return redirect()
            ->route('admin.roles-permissions.index')
            ->with('success', 'Role assigned to user successfully.');
    }

    public function getUserRoles(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $roles = $user->roles->pluck('name');

        return response()->json($roles);
    }

    /**
     * @param  array<int, int>  $permissionIds
     */
    private function selectedPermissions(array $permissionIds, string $guardName): array
    {
        $dashboardPermission = Permission::findOrCreate('dashboard.view', $guardName);
        $selectedPermissions = Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('id', $permissionIds)
            ->whereIn('name', $this->availablePermissionNames())
            ->where('name', '!=', 'dashboard.view')
            ->get()
            ->all();

        return [...$selectedPermissions, $dashboardPermission];
    }

    /**
     * @return array<int, string>
     */
    private function availablePermissionNames(): array
    {
        $permissionNames = [];

        foreach (config('admin_permissions', []) as $module => $definition) {
            foreach (array_keys($definition['permissions']) as $action) {
                $permissionNames[] = "{$module}.{$action}";
            }
        }

        return $permissionNames;
    }
}
