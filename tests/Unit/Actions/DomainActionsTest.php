<?php

namespace Tests\Unit\Actions;

use App\Actions\ChannelCdns\DeleteChannelCdnAction;
use App\Actions\ChannelCdns\StoreChannelCdnAction;
use App\Actions\ChannelCdns\UpdateChannelCdnAction;
use App\Actions\ChannelGroups\DeleteChannelGroupAction;
use App\Actions\ChannelGroups\StoreChannelGroupAction;
use App\Actions\ChannelGroups\UpdateChannelGroupAction;
use App\Actions\Channels\DeleteChannelAction;
use App\Actions\Channels\StoreChannelAction;
use App\Actions\Channels\UpdateChannelAction;
use App\Actions\ChannelUrls\DeleteChannelUrlAction;
use App\Actions\ChannelUrls\StoreChannelUrlAction;
use App\Actions\ChannelUrls\UpdateChannelUrlAction;
use App\Actions\CustomerInvoces\CancelCustomerInvoceAction;
use App\Actions\CustomerInvoces\StoreCustomerInvoceAction;
use App\Actions\CustomerPlanAdditionals\AddCustomerPlanAdditionalAction;
use App\Actions\CustomerPlanGroups\AddChannelGroupToCustomerPlanAction;
use App\Actions\CustomerPlanGroups\RemoveChannelGroupFromCustomerPlanAction;
use App\Actions\CustomerPlans\DeleteCustomerPlanAction;
use App\Actions\CustomerPlans\StoreCustomerPlanAction;
use App\Actions\CustomerPlans\UpdateCustomerPlanAction;
use App\Actions\Customers\DeleteCustomerAction;
use App\Actions\Customers\StoreCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\CustomerPlan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DomainActionsTest extends TestCase
{
    use RefreshDatabase;

    protected array $logosToRemove = [];

    protected function tearDown(): void
    {
        foreach ($this->logosToRemove as $relativePath) {
            $path = public_path($relativePath);
            if (is_file($path)) {
                unlink($path);
            }
        }

        Date::setTestNow();

        parent::tearDown();
    }

    public function test_channel_actions_store_update_and_delete_channel_with_logo(): void
    {
        $group = ChannelGroup::factory()->create();
        $channel = StoreChannelAction::run([
            'number' => 10,
            'name' => 'News',
            'group_id' => $group->id,
            'image' => 'ignored',
        ], UploadedFile::fake()->create('logo.png', 1, 'image/png'), true);
        $this->logosToRemove[] = $channel->logo;

        $this->assertTrue((bool) $channel->radio);
        $this->assertStringStartsWith('logos/', $channel->logo);
        $this->assertFileExists(public_path($channel->logo));

        $updated = UpdateChannelAction::run($channel, [
            'number' => 11,
            'name' => 'News HD',
            'group_id' => $group->id,
        ], null, false);

        $this->assertSame(11, $updated->fresh()->number);
        $this->assertFalse((bool) $updated->fresh()->radio);

        DeleteChannelAction::run($updated);

        $this->assertDatabaseMissing('iptv_channels', ['id' => $channel->id]);
    }

    public function test_cdn_group_url_plan_customer_and_invoice_actions_persist_expected_state(): void
    {
        $cdn = StoreChannelCdnAction::run(['name' => 'Primary CDN', 'slug' => 'primary']);
        UpdateChannelCdnAction::run($cdn, ['name' => 'Updated CDN', 'slug' => 'updated']);
        $group = StoreChannelGroupAction::run(['name' => 'Sports']);
        UpdateChannelGroupAction::run($group, ['name' => 'Live Sports']);
        $plan = StoreCustomerPlanAction::run(['name' => 'Base', 'price' => 29.90], true, false);
        UpdateCustomerPlanAction::run($plan, ['name' => 'Base HD', 'price' => 39.90], true, false);
        $customer = StoreCustomerAction::run([
            'name' => 'Client',
            'username' => 'client',
            'iptv_plan_id' => $plan->id,
            'iptv_cdn_id' => $cdn->id,
            'active' => true,
            'due_day' => 15,
        ]);
        $channel = Channel::factory()->create(['group_id' => $group->id]);
        $url = StoreChannelUrlAction::run([
            'iptv_cdn_id' => $cdn->id,
            'iptv_channel_id' => $channel->id,
            'url_stream' => 'https://example.test/live.m3u8',
        ]);
        UpdateChannelUrlAction::run($url, [
            'iptv_cdn_id' => $cdn->id,
            'iptv_channel_id' => $channel->id,
            'url_stream' => 'https://example.test/updated.m3u8',
        ]);
        $invoice = StoreCustomerInvoceAction::run($customer->id, ['duedate_at' => '2026-06-15']);
        Date::setTestNow('2026-06-20 12:00:00');
        CancelCustomerInvoceAction::run($invoice);

        $this->assertDatabaseHas('iptv_cdns', ['id' => $cdn->id, 'slug' => 'updated']);
        $this->assertDatabaseHas('iptv_channel_groups', ['id' => $group->id, 'name' => 'Live Sports']);
        $this->assertDatabaseHas('iptv_plans', ['id' => $plan->id, 'name' => 'Base HD']);
        $this->assertDatabaseHas('iptv_customers', ['id' => $customer->id, 'username' => 'client']);
        $this->assertDatabaseHas('iptv_urls', ['id' => $url->id, 'url_stream' => 'https://example.test/updated.m3u8']);
        $this->assertNotNull($invoice->fresh()->canceled_at);

        DeleteChannelUrlAction::run($url);
        $channel->delete();
        CustomerInvoce::query()->whereKey($invoice->id)->delete();
        DeleteCustomerAction::run($customer);
        DeleteCustomerPlanAction::run($plan);
        DeleteChannelGroupAction::run($group);
        DeleteChannelCdnAction::run($cdn);

        $this->assertDatabaseMissing('iptv_urls', ['id' => $url->id]);
        $this->assertDatabaseMissing('iptv_customers', ['id' => $customer->id]);
        $this->assertDatabaseMissing('iptv_plans', ['id' => $plan->id]);
        $this->assertDatabaseMissing('iptv_channel_groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('iptv_cdns', ['id' => $cdn->id]);
    }

    public function test_customer_auth_token_is_securely_hashed_and_regenerates(): void
    {
        Date::setTestNow('2026-06-20 12:00:00');
        $plan = CustomerPlan::factory()->active()->create();
        $cdn = ChannelCdn::factory()->create();

        $customer = StoreCustomerAction::run([
            'name' => 'Secure Client',
            'username' => 'secure-client',
            'iptv_plan_id' => $plan->id,
            'iptv_cdn_id' => $cdn->id,
            'active' => true,
            'due_day' => 15,
        ]);
        $issuedToken = (string) $customer->getRelation('plainAuthToken');
        [$oldTokenId, $oldSecret] = explode('.', $issuedToken, 2);
        $oldTokenHash = (string) $customer->auth_token_hash;

        $this->assertSame($oldTokenId, $customer->auth_token_id);
        $this->assertTrue(Hash::check($oldSecret, $oldTokenHash));

        $updated = UpdateCustomerAction::run($customer, [], true, true, false);
        $newToken = (string) $updated->getRelation('plainAuthToken');
        [$newTokenId, $newSecret] = explode('.', $newToken, 2);
        $fresh = $customer->fresh();

        $this->assertNotSame($oldTokenId, $newTokenId);
        $this->assertNotSame($oldTokenHash, (string) $fresh->auth_token_hash);
        $this->assertTrue(Hash::check($newSecret, (string) $fresh->auth_token_hash));
    }

    public function test_additional_plan_action_is_idempotent_and_requires_additional_plan(): void
    {
        $customer = Customer::factory()->active()->create();
        $additional = CustomerPlan::factory()->active()->additional()->create();
        $main = CustomerPlan::factory()->active()->create();

        AddCustomerPlanAdditionalAction::run($customer, $additional->id);
        AddCustomerPlanAdditionalAction::run($customer, $additional->id);

        $this->assertSame(1, $customer->plans_additional()->whereKey($additional->id)->count());

        $this->expectException(ModelNotFoundException::class);
        AddCustomerPlanAdditionalAction::run($customer, $main->id);
    }

    public function test_group_plan_actions_do_not_remove_or_steal_other_plan_groups(): void
    {
        $plan = CustomerPlan::factory()->active()->create();
        $otherPlan = CustomerPlan::factory()->active()->create();
        $group = ChannelGroup::factory()->create();
        $otherGroup = ChannelGroup::factory()->forPlan($otherPlan)->create();

        AddChannelGroupToCustomerPlanAction::run($plan, $group->id);

        $this->assertSame($plan->id, $group->fresh()->iptv_plan_id);

        try {
            AddChannelGroupToCustomerPlanAction::run($plan, $otherGroup->id);
            $this->fail('Expected validation exception when group belongs to another plan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('iptv_group_id', $exception->errors());
        }

        RemoveChannelGroupFromCustomerPlanAction::run($plan, $group->id);

        $this->assertNull($group->fresh()->iptv_plan_id);
        $this->assertSame($otherPlan->id, $otherGroup->fresh()->iptv_plan_id);
    }
}
