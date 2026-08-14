<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrative_routes_redirect_guests_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_administrative_routes(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        foreach (['dashboard', 'config', 'list_user', 'users.invite'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_administrators_can_access_administrative_routes(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_user_deactivated_during_session_is_logged_out(): void
    {
        $user = User::factory()->create(['active' => true, 'is_admin' => false]);
        $this->actingAs($user);

        $user->update(['active' => false]);

        $this->get(route('user.profile'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_deactivated_during_session_is_logged_out(): void
    {
        $admin = User::factory()->create(['active' => true, 'is_admin' => true]);
        $this->actingAs($admin);

        $admin->update(['active' => false]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_framework_api_user_route_is_protected_or_unavailable_by_design(): void
    {
        $response = $this->getJson('/api/user');

        $this->assertContains(
            $response->getStatusCode(),
            [401, 404],
            'Expected /api/user to be protected (401) or unavailable (404).'
        );
    }

    public function test_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
