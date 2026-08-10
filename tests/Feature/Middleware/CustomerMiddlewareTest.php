<?php

namespace Tests\Feature\Middleware;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use DateTimeInterface;
use Tests\TestCase;

class CustomerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: string, 1: string}
     */
    protected function tokenCredentialsFor(Customer $customer, ?DateTimeInterface $expiresAt = null): array
    {
        $token = $customer->issueAuthToken($expiresAt);
        $parts = explode('.', $token, 2);

        return [$parts[0], $parts[1]];
    }

    protected function defineRoute(): void
    {
        Route::middleware('client')->get('/_testing/client-auth', function (Request $request) {
            return response()->json([
                'customer_id' => $request->attributes->get('customer')?->id,
            ]);
        });
    }

    public function test_missing_credentials_return_laravel_unauthorized_response_with_basic_realm(): void
    {
        $this->defineRoute();

        $response = $this->get('/_testing/client-auth');

        $response->assertUnauthorized();
        $response->assertHeader('WWW-Authenticate', 'Basic realm="Access denied"');
        $response->assertSee('unauthorized');
    }

    public function test_invalid_credentials_do_not_leak_customer_hash(): void
    {
        $this->defineRoute();
        $customer = Customer::factory()->active()->create();
        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);

        $response = $this->withBasicAuth($tokenId, 'wrong')->get('/_testing/client-auth');

        $response->assertUnauthorized();
        $response->assertDontSee($tokenSecret);
    }

    public function test_valid_basic_credentials_attach_customer_to_request_attributes(): void
    {
        $this->defineRoute();
        $customer = Customer::factory()->active()->create();
        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);

        $response = $this->withBasicAuth($tokenId, $tokenSecret)->get('/_testing/client-auth');

        $response->assertOk();
        $response->assertJson([
            'customer_id' => $customer->id,
        ]);

        $this->assertNotNull($customer->fresh()->auth_token_last_used_at);
    }

    public function test_query_credentials_are_rejected_for_security_reasons(): void
    {
        $this->defineRoute();
        $customer = Customer::factory()->active()->create();
        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);

        $response = $this->get('/_testing/client-auth?user=' . $tokenId . '&pass=' . $tokenSecret);

        $response->assertUnauthorized();
    }

    public function test_inactive_and_defeated_customers_are_blocked(): void
    {
        $this->defineRoute();
        $inactive = Customer::factory()->inactive()->create();
        $defeated = Customer::factory()->active()->create();
        CustomerInvoce::factory()->overdue()->create(['iptv_customer_id' => $defeated->id]);
        [$inactiveTokenId, $inactiveSecret] = $this->tokenCredentialsFor($inactive);
        [$defeatedTokenId, $defeatedSecret] = $this->tokenCredentialsFor($defeated);

        $this->withBasicAuth($inactiveTokenId, $inactiveSecret)
            ->get('/_testing/client-auth')
            ->assertUnauthorized()
            ->assertSee('not Active');

        $this->withBasicAuth($defeatedTokenId, $defeatedSecret)
            ->get('/_testing/client-auth')
            ->assertUnauthorized()
            ->assertSee('defeated');
    }

    public function test_revoked_and_expired_tokens_are_blocked(): void
    {
        $this->defineRoute();
        $revoked = Customer::factory()->active()->create();
        $expired = Customer::factory()->active()->create();

        [$revokedTokenId, $revokedSecret] = $this->tokenCredentialsFor($revoked);
        $revoked->revokeAuthToken();

        [$expiredTokenId, $expiredSecret] = $this->tokenCredentialsFor($expired, now()->subMinute());

        $this->withBasicAuth($revokedTokenId, $revokedSecret)
            ->get('/_testing/client-auth')
            ->assertUnauthorized();

        $this->withBasicAuth($expiredTokenId, $expiredSecret)
            ->get('/_testing/client-auth')
            ->assertUnauthorized();
    }
}
