<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_roles_and_permissions_from_the_sidebar(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.roles-permissions.index'))
            ->assertOk()
            ->assertSee('Roles &amp; Permissions')
            ->assertSee('Create a role')
            ->assertSee('Destinations: Create')
            ->assertSee('Dashboard: Always included')
            ->assertSee(route('admin.roles-permissions.index'));
    }

    public function test_non_admin_cannot_open_roles_and_permissions(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.roles-permissions.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_role_without_permissions(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.roles-permissions.create-role'), [
                'name' => 'Destination Manager',
            ])
            ->assertRedirect(route('admin.roles-permissions.index'))
            ->assertSessionHas('success', 'Role created successfully.');

        $this->assertDatabaseHas('roles', [
            'name' => 'Destination Manager',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_create_a_role_with_selected_permissions(): void
    {
        $permission = Permission::create([
            'name' => 'destinations.create',
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->post(route('admin.roles-permissions.create-role'), [
                'name' => 'Booking Manager',
                'permissions' => [$permission->id],
            ])
            ->assertRedirect(route('admin.roles-permissions.index'))
            ->assertSessionHas('success', 'Role created successfully.');

        $this->assertDatabaseHas('roles', [
            'name' => 'Booking Manager',
            'guard_name' => 'web',
        ]);
        $role = Role::query()->where('name', 'Destination Manager')->sole();
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_admin_can_create_a_role_and_login_account_with_limited_dashboard_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.roles-permissions.index'))->assertOk();

        $destinationView = Permission::findByName('destinations.view', 'web');
        $destinationCreate = Permission::findByName('destinations.create', 'web');

        $this->actingAs($admin)
            ->post(route('admin.roles-permissions.create-role'), [
                'name' => 'Manager',
                'permissions' => [$destinationView->id, $destinationCreate->id],
                'account_name' => 'Destination Manager',
                'account_email' => 'manager@tourtravel.test',
                'account_password' => 'Manager@12345',
                'account_password_confirmation' => 'Manager@12345',
            ])
            ->assertRedirect(route('admin.roles-permissions.index'))
            ->assertSessionHas('created_account.email', 'manager@tourtravel.test');

        $manager = User::query()->where('email', 'manager@tourtravel.test')->sole();
        $role = Role::query()->where('name', 'Manager')->sole();

        $this->assertTrue($manager->hasRole($role));
        $this->assertTrue($manager->hasPermissionTo('dashboard.view'));
        $this->assertTrue($manager->hasPermissionTo('destinations.create'));
        $this->assertTrue(Hash::check('Manager@12345', $manager->password));

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.post'), [
            'email' => 'manager@tourtravel.test',
            'password' => 'Manager@12345',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($manager);
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.destinations.index'))
            ->assertDontSee(route('admin.packages.index'))
            ->assertDontSee(route('admin.roles-permissions.index'));
        $this->get(route('admin.countries.index'))->assertOk();
        $this->get(route('admin.countries.create'))->assertOk();
        $this->get(route('admin.packages.index'))->assertForbidden();
        $this->get(route('admin.roles-permissions.index'))->assertForbidden();
    }

    public function test_view_permission_does_not_allow_creating_destinations(): void
    {
        $permission = Permission::create([
            'name' => 'destinations.view',
            'guard_name' => 'web',
        ]);
        $role = Role::create([
            'name' => 'Destination Viewer',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);
        $role->givePermissionTo(Permission::create([
            'name' => 'dashboard.view',
            'guard_name' => 'web',
        ]));
        $manager = User::factory()->create(['is_admin' => false]);
        $manager->assignRole($role);

        $this->actingAs($manager)
            ->get(route('admin.countries.create'))
            ->assertForbidden();
    }
}
