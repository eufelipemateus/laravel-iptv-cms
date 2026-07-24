<?php

namespace Tests\Unit\Models;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\CustomerPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_model_relationships_resolve_the_expected_records(): void
    {
        $plan = CustomerPlan::factory()->active()->create();
        $group = ChannelGroup::factory()->forPlan($plan)->create();
        $channel = Channel::factory()->create(['group_id' => $group->id]);
        $cdn = ChannelCdn::factory()->create();
        $url = ChannelUrl::factory()->create([
            'iptv_channel_id' => $channel->id,
            'iptv_cdn_id' => $cdn->id,
        ]);
        $customer = Customer::factory()->active()->create([
            'iptv_plan_id' => $plan->id,
            'iptv_cdn_id' => $cdn->id,
        ]);
        $invoice = CustomerInvoce::factory()->create(['iptv_customer_id' => $customer->id]);

        $this->assertTrue($channel->group->is($group));
        $this->assertTrue($group->plan->is($plan));
        $this->assertTrue($cdn->customers->first()->is($customer));
        $this->assertTrue($customer->plan->is($plan));
        $this->assertTrue($customer->cdn->is($cdn));
        $this->assertTrue($invoice->customer->is($customer));
        $this->assertDatabaseHas('iptv_urls', ['id' => $url->id]);
    }

    public function test_channel_list_scope_orders_radio_then_number(): void
    {
        Channel::factory()->radio()->create(['number' => 1]);
        $firstTv = Channel::factory()->create(['number' => 2]);
        $secondTv = Channel::factory()->create(['number' => 3]);

        $this->assertSame([$firstTv->id, $secondTv->id], Channel::getList()->take(2)->pluck('id')->all());
    }
}
