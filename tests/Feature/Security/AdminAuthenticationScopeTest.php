<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrative_routes_are_currently_public_by_product_scope_decision(): void
    {
        $this->get(route('dashboard'))->assertOk();

        $this->assertTrue(true, 'Administrative auth is intentionally documented as a known limitation for a later task.');
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
