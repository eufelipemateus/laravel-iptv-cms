<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_authenticated_user_and_restores_an_update(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $group = ChannelGroup::factory()->create(['name' => 'Original']);

        $this->actingAs($admin);
        $group->update(['name' => 'Changed']);

        $audit = AuditLog::query()->where('event', 'updated')->latest('id')->firstOrFail();
        $this->assertSame($admin->id, $audit->user_id);
        $this->assertSame('Original', $audit->old_values['name']);
        $this->assertSame('Changed', $audit->new_values['name']);

        $this->post(route('audit.restore', $audit))->assertRedirect();

        $this->assertSame('Original', $group->fresh()->name);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'restored',
            'restored_from_id' => $audit->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_it_restores_a_deleted_record(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $group = ChannelGroup::factory()->create();
        $groupId = $group->id;

        $this->actingAs($admin);
        $group->delete();
        $audit = AuditLog::query()->where('event', 'deleted')->latest('id')->firstOrFail();

        $this->post(route('audit.restore', $audit))->assertRedirect();

        $this->assertDatabaseHas('iptv_channel_groups', ['id' => $groupId]);
    }

    public function test_non_admin_cannot_access_audit_log(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'active' => true]);

        $this->actingAs($user)->get(route('audit.index'))->assertForbidden();
    }
}
