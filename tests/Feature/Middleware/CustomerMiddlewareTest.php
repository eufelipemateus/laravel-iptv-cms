<?php

namespace Tests\Feature\Middleware;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CustomerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoute(): void
    {
        Route::middleware('client')->get('/_testing/client-auth', function (Request $request) {
            return response()->json([
                'customer_id' => $request->attributes->get('customer')?->id,
                'legacy_customer_id' => $request->attributes->get('custormer')?->id,
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

        $response = $this->withBasicAuth($customer->username, 'wrong')->get('/_testing/client-auth');

        $response->assertUnauthorized();
        $response->assertDontSee($customer->hash_acess);
    }

    public function test_valid_basic_credentials_attach_customer_to_request_attributes(): void
    {
        $this->defineRoute();
        $customer = Customer::factory()->active()->create();

        $response = $this->withBasicAuth($customer->username, $customer->hash_acess)->get('/_testing/client-auth');

        $response->assertOk();
        $response->assertJson([
            'customer_id' => $customer->id,
            'legacy_customer_id' => $customer->id,
        ]);
    }

    public function test_personal_url_query_credentials_remain_supported(): void
    {
        $this->defineRoute();
        $customer = Customer::factory()->active()->create();

        $response = $this->get('/_testing/client-auth?user='.$customer->username.'&pass='.$customer->hash_acess);

        $response->assertOk();
        $response->assertJson(['customer_id' => $customer->id]);
    }

    public function test_inactive_and_defeated_customers_are_blocked(): void
    {
        $this->defineRoute();
        $inactive = Customer::factory()->inactive()->create();
        $defeated = Customer::factory()->active()->create();
        CustomerInvoce::factory()->overdue()->create(['iptv_customer_id' => $defeated->id]);

        $this->withBasicAuth($inactive->username, $inactive->hash_acess)
            ->get('/_testing/client-auth')
            ->assertUnauthorized()
            ->assertSee('not Active');

        $this->withBasicAuth($defeated->username, $defeated->hash_acess)
            ->get('/_testing/client-auth')
            ->assertUnauthorized()
            ->assertSee('defeated');
    }
}
