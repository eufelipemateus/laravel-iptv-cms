<?php

namespace Tests\Feature\Playlists;

use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIptvFixtures;
use Tests\TestCase;

class PrivatePlaylistTest extends TestCase
{
    use BuildsIptvFixtures;
    use RefreshDatabase;

    /**
     * @return array{0: string, 1: string}
     */
    protected function tokenCredentialsFor(Customer $customer): array
    {
        $token = $customer->issueAuthToken();
        $parts = explode('.', $token, 2);

        return [$parts[0], $parts[1]];
    }

    public function test_private_playlist_requires_customer_basic_auth_and_returns_authorized_plan_channels(): void
    {
        $cdn = ChannelCdn::factory()->create(['slug' => 'customer-cdn']);
        $mainPlan = CustomerPlan::factory()->active()->create();
        $additionalPlan = CustomerPlan::factory()->active()->additional()->create();
        $customer = Customer::factory()->active()->create([
            'iptv_plan_id' => $mainPlan->id,
            'iptv_cdn_id' => $cdn->id,
        ]);
        $customer->plans_additional()->syncWithoutDetaching([$additionalPlan->id]);

        $this->makePlayableChannel($cdn, $mainPlan, ['number' => 1, 'name' => 'Main']);
        $this->makePlayableChannel($cdn, $additionalPlan, ['number' => 2, 'name' => 'Extra']);
        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);

        $this->get(route('client-playlist', ['slug' => $cdn->slug]))->assertUnauthorized();

        $response = $this->withBasicAuth($tokenId, $tokenSecret)
            ->get(route('client-playlist', ['slug' => $cdn->slug]));

        $response->assertOk();
        $response->assertSee('#EXTM3U', false);
        $response->assertSee('Main', false);
        $response->assertSee('Extra', false);
    }

    public function test_private_playlist_rejects_cdn_slug_that_does_not_belong_to_customer(): void
    {
        $customerCdn = ChannelCdn::factory()->create(['slug' => 'customer-cdn']);
        $otherCdn = ChannelCdn::factory()->create(['slug' => 'other-cdn']);
        $customer = Customer::factory()->active()->create(['iptv_cdn_id' => $customerCdn->id]);
        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);

        $this->withBasicAuth($tokenId, $tokenSecret)
            ->get(route('client-playlist', ['slug' => $otherCdn->slug]))
            ->assertNotFound();
    }
}
