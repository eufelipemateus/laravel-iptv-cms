<?php

namespace Tests\Feature\Requests;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\CustomerPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_creation_rejects_additional_or_inactive_plan_as_primary_and_invalid_email(): void
    {
        $additionalPlan = CustomerPlan::factory()->active()->additional()->create();
        $inactivePlan = CustomerPlan::factory()->inactive()->create();
        $cdn = ChannelCdn::factory()->create();

        $payload = [
            'name' => 'Client',
            'username' => 'client',
            'iptv_cdn_id' => $cdn->id,
            'due_day' => 15,
            'email' => 'not-an-email',
        ];

        $this->from(route('add_customer'))->post(route('create_customer'), $payload + [
            'iptv_plan_id' => $additionalPlan->id,
        ])->assertSessionHasErrors(['iptv_plan_id', 'email']);

        $this->from(route('add_customer'))->post(route('create_customer'), $payload + [
            'username' => 'client-2',
            'iptv_plan_id' => $inactivePlan->id,
            'email' => 'client@example.test',
        ])->assertSessionHasErrors(['iptv_plan_id']);
    }

    public function test_customer_plan_payload_can_omit_tax_id(): void
    {
        $this->post(route('create_customer_plan'), [
            'name' => 'Main Plan',
            'price' => '19.90',
            'active' => '1',
        ])->assertRedirect(route('list_customer_plan'));

        $this->assertDatabaseHas('iptv_plans', [
            'name' => 'Main Plan',
            'active' => 1,
            'additional' => 0,
            'iptv_tax_vat_id' => null,
        ]);
    }

    public function test_channel_url_rejects_malformed_and_crlf_urls(): void
    {
        $cdn = ChannelCdn::factory()->create();
        $channel = Channel::factory()->create();

        $this->from(route('show_channel', ['id' => $channel->id]))
            ->post(route('create_channel_url'), [
                'iptv_cdn_id' => $cdn->id,
                'iptv_channel_id' => $channel->id,
                'url_stream' => "https://example.test/live.m3u8\r\n#EXTINF:-1,Injected",
            ])
            ->assertSessionHasErrors(['url_stream']);

        $this->from(route('show_channel', ['id' => $channel->id]))
            ->post(route('create_channel_url'), [
                'iptv_cdn_id' => $cdn->id,
                'iptv_channel_id' => $channel->id,
                'url_stream' => 'not a url',
            ])
            ->assertSessionHasErrors(['url_stream']);
    }

    public function test_invoice_pay_and_cancel_requests_reject_invoice_from_another_customer(): void
    {
        $customerA = Customer::factory()->active()->create();
        $customerB = Customer::factory()->active()->create();
        $invoiceB = CustomerInvoce::factory()->create(['iptv_customer_id' => $customerB->id]);

        $this->from(route('show_customer', ['id' => $customerA->id]))
            ->post(route('pay_customer_invoce', [
                'customer_id' => $customerA->id,
                'id' => $invoiceB->id,
            ]))
            ->assertSessionHasErrors(['id']);

        $this->from(route('show_customer', ['id' => $customerA->id]))
            ->post(route('cancel_customer_invoce', [
                'customer_id' => $customerA->id,
                'id' => $invoiceB->id,
            ]))
            ->assertSessionHasErrors(['id']);
    }

    public function test_delete_requests_require_existing_route_id(): void
    {
        $this->post(route('delete_channel', ['id' => 999999]))->assertSessionHasErrors(['id']);
        $this->post(route('delete_channel_cdn', ['id' => 999999]))->assertSessionHasErrors(['id']);
        $this->post(route('delete_channel_group', ['id' => 999999]))->assertSessionHasErrors(['id']);
        $this->post(route('delete_customer_plan', ['id' => 999999]))->assertSessionHasErrors(['id']);
        $this->post(route('delete_customer', ['id' => 999999]))->assertSessionHasErrors(['id']);
        $this->post(route('delete_channel_url', ['id' => 999999]))->assertSessionHasErrors(['id']);
    }
}
