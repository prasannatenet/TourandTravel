<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Email Address');
    }

    public function test_admin_can_login_with_email_and_password(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@tourtravel.com',
            'password' => bcrypt('Admin@123456'),
            'is_admin' => true,
        ]);

        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@tourtravel.com',
            'password' => 'Admin@123456',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect('/admin/login');
    }
}
