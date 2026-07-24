<?php

namespace Tests\Unit\Models;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIptvFixtures;
use Tests\TestCase;

class ChannelScopesTest extends TestCase
{
    use BuildsIptvFixtures;
    use RefreshDatabase;

    public function test_public_playlist_scope_returns_only_requested_cdn_ordered_by_number(): void
    {
        $targetCdn = ChannelCdn::factory()->create(['slug' => 'target']);
        $otherCdn = ChannelCdn::factory()->create(['slug' => 'other']);

        $second = $this->makePlayableChannel($targetCdn, null, ['number' => 20, 'name' => 'Second']);
        $first = $this->makePlayableChannel($targetCdn, null, ['number' => 10, 'name' => 'First']);
        $this->makePlayableChannel($otherCdn, null, ['number' => 1, 'name' => 'Other CDN']);

        $list = Channel::getListM3u8('target');

        $this->assertSame([$first->name, $second->name], $list->pluck('name')->all());
    }

    public function test_customer_playlist_scope_merges_main_and_additional_plan_channels(): void
    {
        $cdn = ChannelCdn::factory()->create(['slug' => 'customer-cdn']);
        $mainPlan = CustomerPlan::factory()->active()->create();
        $additionalPlan = CustomerPlan::factory()->active()->additional()->create();
        $unauthorizedPlan = CustomerPlan::factory()->active()->create();
        $customer = Customer::factory()->active()->create([
            'iptv_plan_id' => $mainPlan->id,
            'iptv_cdn_id' => $cdn->id,
        ]);
        $customer->plans_additional()->syncWithoutDetaching([$additionalPlan->id]);

        $mainChannel = $this->makePlayableChannel($cdn, $mainPlan, ['number' => 1, 'name' => 'Main']);
        $additionalChannel = $this->makePlayableChannel($cdn, $additionalPlan, ['number' => 2, 'name' => 'Additional']);
        $this->makePlayableChannel($cdn, $unauthorizedPlan, ['number' => 3, 'name' => 'Unauthorized']);

        $list = Channel::getCustomerChannelListM3u8('customer-cdn', $customer->id);

        $this->assertSame([$mainChannel->name, $additionalChannel->name], $list->pluck('name')->all());
    }
}
