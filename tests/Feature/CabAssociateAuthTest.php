<?php

namespace Tests\Feature;

use App\Models\CabAssociate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CabAssociateAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_associate_can_sign_in_and_view_their_dashboard_details(): void
    {
        $associate = $this->createAssociate();

        $this->get(route('cab-associate.login'))
            ->assertOk()
            ->assertSee('Cab Associate Portal');

        $this->post(route('cab-associate.login.post'), [
            'username' => $associate->username,
            'password' => 'StrongCabPassword123!',
        ])->assertRedirect(route('cab-associate.dashboard'))
            ->assertSessionHas('cab_associate_id', $associate->id);

        $this->get(route('cab-associate.dashboard'))
            ->assertOk()
            ->assertSee($associate->name)
            ->assertSee($associate->username)
            ->assertSee($associate->phone)
            ->assertSee('Your associate details')
            ->assertDontSee('StrongCabPassword123!');
    }

    public function test_invalid_or_inactive_associate_credentials_are_rejected(): void
    {
        $associate = $this->createAssociate(['status' => 'inactive']);

        $this->from(route('cab-associate.login'))
            ->post(route('cab-associate.login.post'), [
                'username' => $associate->username,
                'password' => 'StrongCabPassword123!',
            ])
            ->assertRedirect(route('cab-associate.login'))
            ->assertSessionHasErrors([
                'username' => 'The username or password is incorrect, or the account is inactive.',
            ])
            ->assertSessionMissing('cab_associate_id');
    }

    public function test_guest_is_redirected_to_associate_login_from_dashboard(): void
    {
        $this->get(route('cab-associate.dashboard'))
            ->assertRedirect(route('cab-associate.login'));
    }

    private function createAssociate(array $attributes = []): CabAssociate
    {
        return CabAssociate::query()->create(array_merge([
            'name' => 'North Star Travels',
            'username' => 'north-star',
            'password' => 'StrongCabPassword123!',
            'phone' => '9876543210',
            'email' => 'northstar@example.test',
            'address' => 'Jaipur',
            'commission_rate' => 5,
            'status' => 'active',
        ], $attributes));
    }
}
