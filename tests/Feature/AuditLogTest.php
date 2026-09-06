<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\Customer;
use App\Models\User;
use App\Services\Audit\AuditPayloadSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_records_create_update_and_delete_with_only_changed_attributes(): void
    {
        $admin = $this->actingAsAdmin();

        $group = ChannelGroup::factory()->create(['name' => 'Original']);
        $created = $this->latestAudit('created');

        $this->assertSame($admin->id, $created->user_id);
        $this->assertSame('Original', $created->new_values['name']);

        $group->update(['name' => 'Changed']);
        $updated = $this->latestAudit('updated');

        $this->assertSame(['name' => 'Original'], $updated->old_values);
        $this->assertSame(['name' => 'Changed'], $updated->new_values);

        $group->delete();
        $deleted = $this->latestAudit('deleted');

        $this->assertSame((string) $group->id, $deleted->auditable_id);
        $this->assertNull($deleted->new_values);
    }

    public function test_observer_ignores_timestamp_only_updates(): void
    {
        $this->actingAsAdmin();
        $group = ChannelGroup::factory()->create();
        $auditCount = AuditLog::query()->count();

        $group->touch();

        $this->assertSame($auditCount, AuditLog::query()->count());
    }

    public function test_observer_ignores_updates_containing_only_hidden_attributes(): void
    {
        $this->actingAsAdmin();
        $customer = Customer::factory()->create();
        $auditCount = AuditLog::query()->count();

        $customer->issueAuthToken();

        $this->assertSame($auditCount, AuditLog::query()->count());
    }

    public function test_observer_excludes_configured_and_model_hidden_attributes(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
            'invitation_token' => 'secret-invitation',
        ]);
        $audit = AuditLog::query()
            ->where('event', 'created')
            ->where('auditable_type', User::class)
            ->where('auditable_id', (string) $user->id)
            ->firstOrFail();

        $this->assertArrayNotHasKey('password', $audit->new_values);
        $this->assertArrayNotHasKey('remember_token', $audit->new_values);
        $this->assertArrayNotHasKey('invitation_token', $audit->new_values);
    }

    public function test_observer_allows_system_mutations_without_a_user(): void
    {
        AuditLog::query()->delete();

        $group = ChannelGroup::factory()->create();
        $audit = AuditLog::query()
            ->where('auditable_type', ChannelGroup::class)
            ->where('auditable_id', (string) $group->id)
            ->firstOrFail();

        $this->assertNull($audit->user_id);
    }

    public function test_admin_index_uses_cursor_pagination_without_duplicate_records(): void
    {
        $admin = $this->actingAsAdmin();

        foreach (range(1, 30) as $id) {
            $this->createAudit([
                'user_id' => $admin->id,
                'event' => 'created',
                'auditable_id' => (string) $id,
            ]);
        }

        $firstResponse = $this->get(route('audit.index', ['event' => 'created']))->assertOk();
        $firstPage = $firstResponse->viewData('audits');

        $this->assertInstanceOf(CursorPaginator::class, $firstPage);
        $this->assertCount(25, $firstPage->items());
        $this->assertStringContainsString('event=created', $firstPage->nextPageUrl());

        $firstIds = collect($firstPage->items())->pluck('id');
        $secondResponse = $this->get($firstPage->nextPageUrl())->assertOk();
        $secondPage = $secondResponse->viewData('audits');
        $secondIds = collect($secondPage->items())->pluck('id');

        $this->assertCount(5, $secondPage->items());
        $this->assertSame([], $firstIds->intersect($secondIds)->values()->all());
        $this->assertSame($firstIds->max(), $firstIds->first());
        $this->assertSame($secondIds->max(), $secondIds->first());
    }

    public function test_index_selects_metadata_only_and_eager_loads_users(): void
    {
        $admin = $this->actingAsAdmin();
        $this->createAudit([
            'user_id' => $admin->id,
            'old_values' => ['name' => 'Before'],
            'new_values' => ['name' => 'After'],
            'url' => 'https://example.test/large-payload',
            'user_agent' => 'Test browser',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('audit.index'))->assertOk();
        $audit = collect($response->viewData('audits')->items())->first();
        $userQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query): bool => str_contains(strtolower($query['query']), 'from "users"'));

        $this->assertTrue($audit->relationLoaded('user'));
        $this->assertCount(1, $userQueries);
        $this->assertArrayNotHasKey('old_values', $audit->getAttributes());
        $this->assertArrayNotHasKey('new_values', $audit->getAttributes());
        $this->assertArrayNotHasKey('url', $audit->getAttributes());
        $this->assertArrayNotHasKey('ip_address', $audit->getAttributes());
        $this->assertArrayNotHasKey('user_agent', $audit->getAttributes());
    }

    public function test_structured_filters_can_be_applied_individually_and_in_combinations(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $otherUser = User::factory()->create(['is_admin' => true, 'active' => true]);
        $this->actingAs($admin);
        AuditLog::query()->delete();

        $first = $this->createAudit([
            'user_id' => $admin->id,
            'event' => 'created',
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => '10',
            'created_at' => Carbon::parse('2026-08-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-08-01 10:00:00'),
        ]);
        $second = $this->createAudit([
            'user_id' => $otherUser->id,
            'event' => 'updated',
            'auditable_type' => Channel::class,
            'auditable_id' => '20',
            'created_at' => Carbon::parse('2026-08-10 10:00:00'),
            'updated_at' => Carbon::parse('2026-08-10 10:00:00'),
        ]);
        $third = $this->createAudit([
            'user_id' => $otherUser->id,
            'event' => 'deleted',
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => '30',
            'created_at' => Carbon::parse('2026-08-20 10:00:00'),
            'updated_at' => Carbon::parse('2026-08-20 10:00:00'),
        ]);

        $this->assertSame([$second->id], $this->filteredAuditIds(['event' => 'updated']));
        $this->assertSame([$second->id], $this->filteredAuditIds(['auditable_type' => Channel::class]));
        $this->assertSame([$first->id], $this->filteredAuditIds(['auditable_id' => '10']));
        $this->assertSame([$third->id, $second->id], $this->filteredAuditIds(['user_id' => $otherUser->id]));
        $this->assertSame([$third->id, $second->id], $this->filteredAuditIds(['date_from' => '2026-08-05']));
        $this->assertSame([$second->id, $first->id], $this->filteredAuditIds(['date_to' => '2026-08-15']));
        $this->assertSame([$second->id], $this->filteredAuditIds([
            'date_from' => '2026-08-05',
            'date_to' => '2026-08-15',
        ]));
        $this->assertSame([$first->id], $this->filteredAuditIds([
            'event' => 'created',
            'auditable_type' => ChannelGroup::class,
        ]));
        $this->assertSame([$third->id], $this->filteredAuditIds([
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => '30',
        ]));
        $this->assertSame([$second->id], $this->filteredAuditIds([
            'user_id' => $otherUser->id,
            'date_from' => '2026-08-05',
            'date_to' => '2026-08-15',
        ]));
    }

    public function test_invalid_structured_filters_are_rejected(): void
    {
        $this->actingAsAdmin();

        $this->get(route('audit.index', ['event' => 'unknown']))
            ->assertSessionHasErrors('event');
        $this->get(route('audit.index', ['auditable_type' => PersonalAccessToken::class]))
            ->assertSessionHasErrors('auditable_type');
        $this->get(route('audit.index', ['user_id' => 'not-numeric']))
            ->assertSessionHasErrors('user_id');
        $this->get(route('audit.index', [
            'date_from' => '2026-08-20',
            'date_to' => '2026-08-10',
        ]))->assertSessionHasErrors('date_to');
    }

    public function test_admin_can_view_full_audit_details(): void
    {
        $admin = $this->actingAsAdmin();
        $audit = $this->createAudit([
            'user_id' => $admin->id,
            'old_values' => ['name' => 'Before value'],
            'new_values' => ['name' => 'After value'],
            'url' => 'https://example.test/channel/1',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Audit test browser',
        ]);

        $this->get(route('audit.show', $audit))
            ->assertOk()
            ->assertSee('Before value')
            ->assertSee('After value')
            ->assertSee('https://example.test/channel/1')
            ->assertSee('203.0.113.10')
            ->assertSee('Audit test browser');
    }

    public function test_non_admin_cannot_access_audit_pages(): void
    {
        $admin = $this->actingAsAdmin();
        $audit = $this->createAudit(['user_id' => $admin->id]);
        $user = User::factory()->create(['is_admin' => false, 'active' => true]);

        $this->actingAs($user)->get(route('audit.index'))->assertForbidden();
        $this->actingAs($user)->get(route('audit.show', $audit))->assertForbidden();
    }

    public function test_updated_model_can_be_restored_exactly_once(): void
    {
        $admin = $this->actingAsAdmin();
        $group = ChannelGroup::factory()->create(['name' => 'Original']);

        $group->update(['name' => 'Changed']);
        $audit = $this->latestAudit('updated');

        $this->post(route('audit.restore', $audit))->assertRedirect();

        $this->assertSame('Original', $group->fresh()->name);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'restored',
            'restored_from_id' => $audit->id,
            'user_id' => $admin->id,
        ]);
        $this->assertSame(1, AuditLog::query()->where('restored_from_id', $audit->id)->count());
        $restoration = AuditLog::query()->where('restored_from_id', $audit->id)->firstOrFail();

        $this->get(route('audit.show', $audit))
            ->assertOk()
            ->assertSee(__('AUDIT_RESTORATION_ENTRY'))
            ->assertSee('#'.$restoration->id);
        $this->get(route('audit.show', $restoration))
            ->assertOk()
            ->assertSee(__('AUDIT_RESTORED_FROM'))
            ->assertSee('#'.$audit->id);

        $this->post(route('audit.restore', $audit))
            ->assertSessionHasErrors('restore');

        $this->assertSame(1, AuditLog::query()->where('restored_from_id', $audit->id)->count());
    }

    public function test_deleted_model_can_be_restored(): void
    {
        $this->actingAsAdmin();
        $group = ChannelGroup::factory()->create();
        $groupId = $group->id;

        $group->delete();
        $audit = $this->latestAudit('deleted');

        $this->post(route('audit.restore', $audit))->assertRedirect();

        $this->assertDatabaseHas('iptv_channel_groups', ['id' => $groupId]);
    }

    public function test_created_model_can_be_reverted(): void
    {
        $this->actingAsAdmin();
        $group = ChannelGroup::factory()->create();
        $audit = AuditLog::query()
            ->where('event', 'created')
            ->where('auditable_type', ChannelGroup::class)
            ->where('auditable_id', (string) $group->id)
            ->firstOrFail();

        $this->post(route('audit.restore', $audit))->assertRedirect();

        $this->assertDatabaseMissing('iptv_channel_groups', ['id' => $group->id]);
        $this->assertSame(1, AuditLog::query()->where('restored_from_id', $audit->id)->count());
    }

    public function test_invalid_model_and_missing_record_restores_return_controlled_errors(): void
    {
        $this->actingAsAdmin();
        $invalidModel = $this->createAudit([
            'event' => 'updated',
            'auditable_type' => PersonalAccessToken::class,
        ]);
        $missingRecord = $this->createAudit([
            'event' => 'updated',
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => '999999',
        ]);
        $restoredAudit = $this->createAudit([
            'event' => 'restored',
            'auditable_type' => ChannelGroup::class,
        ]);

        $this->post(route('audit.restore', $invalidModel))->assertSessionHasErrors('restore');
        $this->post(route('audit.restore', $missingRecord))->assertSessionHasErrors('restore');
        $this->post(route('audit.restore', $restoredAudit))->assertSessionHasErrors('restore');
    }

    public function test_database_unique_guard_is_handled_without_partial_restore(): void
    {
        $this->actingAsAdmin();
        $group = ChannelGroup::factory()->create(['name' => 'Changed']);
        AuditLog::query()->delete();
        $source = $this->createAudit([
            'event' => 'updated',
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => (string) $group->id,
            'old_values' => ['name' => 'Original'],
            'new_values' => ['name' => 'Changed'],
        ]);
        $this->createAudit([
            'event' => 'restored',
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => (string) $group->id,
            'restored_from_id' => $source->id,
        ]);

        $this->post(route('audit.restore', $source))
            ->assertSessionHasErrors('restore');

        $this->assertSame('Changed', $group->fresh()->name);
        $this->assertSame(1, AuditLog::query()->where('restored_from_id', $source->id)->count());
    }

    public function test_restore_never_changes_sensitive_attributes_excluded_from_audit(): void
    {
        $this->actingAsAdmin();
        $originalPassword = Hash::make('original-password');
        $currentPassword = Hash::make('current-password');
        $user = User::factory()->create([
            'name' => 'Original name',
            'password' => $originalPassword,
        ]);

        $user->forceFill([
            'name' => 'Changed name',
            'password' => $currentPassword,
        ])->save();
        $audit = $this->latestAudit('updated');

        $this->assertArrayNotHasKey('password', $audit->old_values);
        $this->assertArrayNotHasKey('password', $audit->new_values);

        $this->post(route('audit.restore', $audit))->assertRedirect();

        $this->assertSame('Original name', $user->fresh()->name);
        $this->assertSame($currentPassword, $user->fresh()->password);
    }

    public function test_large_request_metadata_is_truncated_and_can_be_persisted(): void
    {
        config([
            'audit.metadata.max_url_length' => 80,
            'audit.metadata.max_user_agent_length' => 40,
        ]);
        $request = Request::create('https://example.test/audit?value='.str_repeat('x', 500));
        $request->headers->set('User-Agent', str_repeat('browser', 100));
        $metadata = app(AuditPayloadSanitizer::class)->requestMetadata($request);

        $audit = $this->createAudit($metadata);

        $this->assertSame(80, strlen($audit->url));
        $this->assertSame(40, strlen($audit->user_agent));
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $this->actingAs($admin);
        AuditLog::query()->delete();

        return $admin;
    }

    /** @param array<string, mixed> $attributes */
    private function createAudit(array $attributes = []): AuditLog
    {
        return AuditLog::query()->create(array_merge([
            'event' => 'updated',
            'auditable_type' => ChannelGroup::class,
            'auditable_id' => '1',
            'old_values' => null,
            'new_values' => null,
        ], $attributes));
    }

    private function latestAudit(string $event): AuditLog
    {
        return AuditLog::query()->where('event', $event)->latest('id')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<int>
     */
    private function filteredAuditIds(array $filters): array
    {
        /** @var TestResponse $response */
        $response = $this->get(route('audit.index', $filters))->assertOk();

        return collect($response->viewData('audits')->items())
            ->pluck('id')
            ->all();
    }
}
