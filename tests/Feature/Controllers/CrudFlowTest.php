<?php

namespace Tests\Feature\Controllers;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\CustomerPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CrudFlowTest extends TestCase
{
    use RefreshDatabase;

    protected array $logosToRemove = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    protected function tearDown(): void
    {
        foreach ($this->logosToRemove as $relativePath) {
            $path = public_path($relativePath);
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_dashboard_and_config_pages_render(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get(route('dashboard'))->assertOk()->assertViewIs('dashboard');
        $this->get(route('config'))->assertOk()->assertViewIs('config');

        $this->post(route('config_save'), ['CURRENT_LOCALE' => 'en'])
            ->assertRedirect(route('config'));
        $this->assertDatabaseHas('iptv_configs', [
            'name' => 'CURRENT_LOCALE',
            'val' => 'en',
            'type' => 'locale',
        ]);
    }

    public function test_large_lists_are_paginated_at_database_level(): void
    {
        $plan = CustomerPlan::factory()->create();
        $cdn = ChannelCdn::factory()->create();
        $group = ChannelGroup::factory()->create();

        Channel::factory()->count(26)->create(['group_id' => $group->id]);
        Customer::factory()->count(26)->create(['iptv_plan_id' => $plan->id, 'iptv_cdn_id' => $cdn->id]);
        ChannelCdn::factory()->count(25)->create();
        ChannelGroup::factory()->count(25)->create();
        CustomerPlan::factory()->count(25)->create();

        foreach (['list_channel', 'list_customer', 'list_channel_cdn', 'list_channel_group', 'list_customer_plan'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertViewHas('list', fn ($list): bool => $list instanceof LengthAwarePaginator
                    && $list->count() === 25
                    && $list->hasMorePages());
        }
    }

    public function test_channel_group_crud_endpoints(): void
    {
        $this->get(route('list_channel_group'))->assertOk()->assertViewIs('channel_group_list');
        $this->get(route('add_channel_group'))->assertOk()->assertViewIs('channel_group');

        $this->post(route('create_channel_group'), ['name' => 'Sports'])
            ->assertRedirect(route('list_channel_group'));
        $group = ChannelGroup::where('name', 'Sports')->firstOrFail();

        $this->get(route('show_channel_group', ['id' => $group->id]))
            ->assertOk()
            ->assertViewIs('channel_group');

        $this->post(route('update_channel_group', ['id' => $group->id]), ['name' => 'Live Sports'])
            ->assertRedirect(route('list_channel_group'));
        $this->assertDatabaseHas('iptv_channel_groups', ['id' => $group->id, 'name' => 'Live Sports']);

        $this->post(route('delete_channel_group', ['id' => $group->id]))
            ->assertRedirect(route('list_channel_group'));
        $this->assertDatabaseMissing('iptv_channel_groups', ['id' => $group->id]);
    }

    public function test_cdn_crud_endpoints(): void
    {
        $this->get(route('list_channel_cdn'))->assertOk()->assertViewIs('channel_cdn_list');
        $this->get(route('add_channel_cdn'))->assertOk()->assertViewIs('channel_cdn');

        $this->post(route('create_channel_cdn'), ['name' => 'Primary CDN', 'slug' => 'primary'])
            ->assertRedirect(route('list_channel_cdn'));
        $cdn = ChannelCdn::where('slug', 'primary')->firstOrFail();

        $this->get(route('show_channel_cdn', ['id' => $cdn->id]))->assertOk()->assertViewIs('channel_cdn');
        $this->post(route('update_channel_cdn', ['id' => $cdn->id]), ['name' => 'Main CDN', 'slug' => 'main'])
            ->assertRedirect(route('list_channel_cdn'));
        $this->assertDatabaseHas('iptv_cdns', ['id' => $cdn->id, 'slug' => 'main']);

        $this->post(route('delete_channel_cdn', ['id' => $cdn->id]))->assertRedirect(route('list_channel_cdn'));
        $this->assertDatabaseMissing('iptv_cdns', ['id' => $cdn->id]);
    }

    public function test_channel_and_url_crud_endpoints(): void
    {
        $group = ChannelGroup::factory()->create();
        $cdn = ChannelCdn::factory()->create();

        $this->get(route('list_channel'))->assertOk()->assertViewIs('channel_list');
        $this->get(route('add_channel'))->assertOk()->assertViewIs('channel');

        $this->post(route('create_channel'), [
            'number' => 101,
            'name' => 'News',
            'group_id' => $group->id,
            'image' => UploadedFile::fake()->image('logo.png', 10, 10),
            'radio' => '1',
        ])->assertRedirect(route('list_channel'));

        $channel = Channel::where('number', 101)->firstOrFail();
        $this->logosToRemove[] = $channel->logo;
        $this->get(route('show_channel', ['id' => $channel->id]))->assertOk()->assertViewIs('channel');

        $this->post(route('update_channel', ['id' => $channel->id]), [
            'number' => 102,
            'name' => 'News HD',
            'group_id' => $group->id,
        ])->assertRedirect(route('list_channel'));
        $this->assertDatabaseHas('iptv_channels', ['id' => $channel->id, 'number' => 102, 'radio' => 0]);

        $this->post(route('create_channel_url'), [
            'iptv_cdn_id' => $cdn->id,
            'iptv_channel_id' => $channel->id,
            'url_stream' => 'https://cdn.test/news.m3u8',
        ])->assertRedirect(route('show_channel', ['id' => $channel->id]));
        $url = ChannelUrl::where('iptv_channel_id', $channel->id)->firstOrFail();

        $this->post(route('update_channel_url', ['id' => $url->id]), [
            'iptv_cdn_id' => $cdn->id,
            'iptv_channel_id' => $channel->id,
            'url_stream' => 'https://cdn.test/news-hd.m3u8',
        ])->assertRedirect(route('show_channel', ['id' => $channel->id]));
        $this->assertDatabaseHas('iptv_urls', ['id' => $url->id, 'url_stream' => 'https://cdn.test/news-hd.m3u8']);

        $this->post(route('delete_channel_url', ['id' => $url->id]))
            ->assertRedirect(route('show_channel', ['id' => $channel->id]));
        $this->post(route('delete_channel', ['id' => $channel->id]))
            ->assertRedirect(route('list_channel'));
        $this->assertDatabaseMissing('iptv_channels', ['id' => $channel->id]);
    }

    public function test_plan_customer_additional_group_and_invoice_endpoints(): void
    {
        $cdn = ChannelCdn::factory()->create();
        $group = ChannelGroup::factory()->create();
        $additional = CustomerPlan::factory()->active()->additional()->create();

        $this->get(route('list_customer_plan'))->assertOk()->assertViewIs('customer_plan_list');
        $this->get(route('add_customer_plan'))->assertOk()->assertViewIs('customer_plan');

        $this->post(route('create_customer_plan'), [
            'name' => 'Base',
            'price' => '29.90',
            'active' => '1',
        ])->assertRedirect(route('list_customer_plan'));
        $plan = CustomerPlan::where('name', 'Base')->firstOrFail();

        $this->get(route('show_customer_plan', ['id' => $plan->id]))
            ->assertOk()
            ->assertViewIs('customer_plan');

        $this->post(route('update_customer_plan', ['id' => $plan->id]), [
            'name' => 'Base Updated',
            'price' => '39.90',
            'active' => '1',
        ])->assertRedirect(route('list_customer_plan'));
        $this->assertDatabaseHas('iptv_plans', [
            'id' => $plan->id,
            'name' => 'Base Updated',
            'price' => 39.90,
        ]);

        $this->post(route('add_group_customer_plan', ['plan_id' => $plan->id]), [
            'iptv_group_id' => $group->id,
        ])->assertRedirect(route('show_customer_plan', ['id' => $plan->id]));
        $this->assertSame($plan->id, $group->fresh()->iptv_plan_id);

        $this->post(route('delete_group_customer_plan', ['plan_id' => $plan->id]), [
            'iptv_group_id' => $group->id,
        ])->assertRedirect(route('show_customer_plan', ['id' => $plan->id]));
        $this->assertNull($group->fresh()->iptv_plan_id);

        $this->post(route('create_customer'), [
            'name' => 'Client',
            'username' => 'client',
            'iptv_plan_id' => $plan->id,
            'iptv_cdn_id' => $cdn->id,
            'due_day' => 15,
            'email' => 'client@example.test',
        ])->assertRedirect();
        $customer = Customer::where('username', 'client')->firstOrFail();

        $this->get(route('list_customer'))->assertOk()->assertViewIs('customer_list');
        $this->get(route('show_customer', ['id' => $customer->id]))
            ->assertOk()
            ->assertViewIs('customer');

        $this->post(route('update_customer', ['id' => $customer->id]), [
            'name' => 'Updated Client',
            'username' => 'client-updated',
            'iptv_plan_id' => $plan->id,
            'iptv_cdn_id' => $cdn->id,
            'due_day' => 20,
            'email' => 'updated-client@example.test',
            'active' => '1',
        ])->assertRedirect(route('show_customer', ['id' => $customer->id]));
        $this->assertDatabaseHas('iptv_customers', [
            'id' => $customer->id,
            'username' => 'client-updated',
            'due_day' => 20,
        ]);

        $this->post(route('add_additional', ['customer_id' => $customer->id]), [
            'iptv_plan_id' => $additional->id,
        ])->assertRedirect(route('show_customer', ['id' => $customer->id]));
        $this->assertSame(1, $customer->plans_additional()->whereKey($additional->id)->count());

        $this->post(route('del_additional', ['customer_id' => $customer->id]), [
            'iptv_plan_id' => $additional->id,
        ])->assertRedirect(route('show_customer', ['id' => $customer->id]));
        $this->assertSame(0, $customer->plans_additional()->whereKey($additional->id)->count());

        $this->get(route('new_customer_invoce', ['customer_id' => $customer->id]))
            ->assertOk()
            ->assertViewIs('customer_invoce');
        $this->post(route('create_customer_invoce', ['customer_id' => $customer->id]), [
            'duedate_at' => '2026-06-15',
        ])->assertRedirect(route('show_customer', ['id' => $customer->id]));
        $invoice = CustomerInvoce::where('iptv_customer_id', $customer->id)->firstOrFail();

        $this->post(route('pay_customer_invoce', ['customer_id' => $customer->id, 'id' => $invoice->id]))
            ->assertOk()
            ->assertViewIs('invoce');

        $this->post(route('cancel_customer_invoce', ['customer_id' => $customer->id, 'id' => $invoice->id]))
            ->assertRedirect(route('show_customer', ['id' => $customer->id]));
        $this->assertNotNull($invoice->fresh()->canceled_at);

        CustomerInvoce::query()->whereKey($invoice->id)->delete();
        $this->post(route('delete_customer', ['id' => $customer->id]))
            ->assertRedirect(route('list_customer'));
        $this->assertDatabaseMissing('iptv_customers', ['id' => $customer->id]);

        $this->post(route('delete_customer_plan', ['id' => $plan->id]))
            ->assertRedirect(route('list_customer_plan'));
        $this->assertDatabaseMissing('iptv_plans', ['id' => $plan->id]);
    }

    public function test_get_requests_do_not_delete_records(): void
    {
        $group = ChannelGroup::factory()->create();

        $this->get('/group/del/'.$group->id)->assertStatus(405);

        $this->assertDatabaseHas('iptv_channel_groups', ['id' => $group->id]);
    }
}
