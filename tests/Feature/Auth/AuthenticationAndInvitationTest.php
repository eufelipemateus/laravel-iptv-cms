<?php

namespace Tests\Feature\Auth;

use App\Actions\Users\InviteUserAction;
use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class AuthenticationAndInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_user_boolean_controls_render_as_switches(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('custom-control custom-switch small', false)
            ->assertSee('class="custom-control-input" name="remember" value="1" type="checkbox"', false);

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false, 'active' => true]);

        $this->actingAs($admin)->get(route('users.invite'))
            ->assertOk()
            ->assertSee('custom-control custom-switch mb-4', false);

        $this->get(route('users.edit', $user))
            ->assertOk()
            ->assertSee('custom-control custom-switch mb-4', false)
            ->assertSee('id="active" class="custom-control-input"', false);
    }

    public function test_admin_user_can_log_in_and_access_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'password' => Hash::make('password')]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_active_non_admin_user_can_log_in_and_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'is_admin' => false,
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'active' => false,
            'password' => Hash::make('password'),
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_invite_a_user(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('users.invite.store'), [
            'name' => 'Nova Pessoa', 'email' => 'nova@example.test', 'is_admin' => '1',
        ])->assertRedirect(route('users.invite'));

        $user = User::where('email', 'nova@example.test')->firstOrFail();
        $this->assertTrue($user->is_admin);
        $this->assertNotNull($user->invitation_token);
        Mail::assertSent(UserInvitationMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_failed_invitation_email_rolls_back_user_creation(): void
    {
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP unavailable'));

        $this->expectException(RuntimeException::class);

        try {
            InviteUserAction::run([
                'name' => 'Falha SMTP',
                'email' => 'smtp-failure@example.test',
            ], false);
        } finally {
            $this->assertDatabaseMissing('users', ['email' => 'smtp-failure@example.test']);
        }
    }

    public function test_admin_accepting_invitation_is_redirected_to_dashboard(): void
    {
        [$user, $token] = $this->invitedUser(true);

        $this->post(route('invitation.accept', $token), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_non_admin_accepting_invitation_is_redirected_to_dashboard(): void
    {
        [$user, $token] = $this->invitedUser(false);

        $this->post(route('invitation.accept', $token), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_page_shows_demo_credentials_in_store_mode(): void
    {
        config()->set('app.env', 'store');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(User::STORE_DEMO_EMAIL)
            ->assertSee(User::STORE_DEMO_PASSWORD);
    }

    public function test_admin_can_search_and_edit_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => 'Marina Silva', 'email' => 'marina@example.test', 'active' => true]);
        User::factory()->create(['name' => 'Outra Pessoa']);

        $this->actingAs($admin)
            ->get(route('list_user', ['search' => 'marina']))
            ->assertOk()
            ->assertSee('Marina Silva')
            ->assertDontSee('Outra Pessoa');

        $this->put(route('users.update', $user), [
            'name' => 'Marina Costa', 'email' => 'costa@example.test', 'is_admin' => '1', 'active' => '0',
        ])->assertRedirect(route('list_user'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Marina Costa', 'is_admin' => 1, 'active' => 0]);
    }

    public function test_admin_can_toggle_active_for_non_admin_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false, 'active' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->assertRedirect(route('list_user'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'active' => 0]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'active' => '1',
            ])
            ->assertRedirect(route('list_user'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'active' => 1]);
    }

    public function test_admin_user_active_status_is_not_changed_by_update_request(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true, 'active' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $otherAdmin), [
                'name' => 'Admin Alterado',
                'email' => 'admin.alterado@example.test',
                'active' => '0',
            ])
            ->assertRedirect(route('list_user'));

        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'name' => 'Admin Alterado',
            'email' => 'admin.alterado@example.test',
            'active' => 1,
        ]);
    }

    public function test_non_admin_user_can_access_users_me(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/users/me')->assertOk()->assertSee($user->email);
    }

    public function test_admin_user_can_access_users_me(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)->get('/users/me')->assertOk()->assertSee($user->email);
    }

    public function test_store_demo_user_cannot_be_updated_in_store_mode(): void
    {
        config()->set('app.env', 'store');

        $user = User::factory()->create([
            'email' => User::STORE_DEMO_EMAIL,
            'name' => 'Demo Store',
        ]);

        $this->expectException(ValidationException::class);

        $user->update(['name' => 'Novo Nome']);
    }

    public function test_store_mode_blocks_user_admin_edit_and_invite_actions(): void
    {
        config()->set('app.env', 'store');

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => 'Pessoa Antiga', 'email' => 'antiga@example.test']);

        $this->actingAs($admin)
            ->get(route('users.edit', $user))
            ->assertRedirect(route('list_user'));

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'name' => 'Pessoa Nova',
                'email' => 'nova@example.test',
                'is_admin' => '1',
            ])
            ->assertRedirect(route('list_user'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Pessoa Antiga',
            'email' => 'antiga@example.test',
        ]);

        $this->actingAs($admin)
            ->post(route('users.invite.store'), [
                'name' => 'Nova Pessoa',
                'email' => 'bloqueado@example.test',
                'is_admin' => '1',
            ])
            ->assertRedirect(route('list_user'));

        $this->assertDatabaseMissing('users', ['email' => 'bloqueado@example.test']);
    }

    /** @return array{User, string} */
    private function invitedUser(bool $isAdmin): array
    {
        $token = 'valid-invitation-token';
        $user = User::factory()->create([
            'is_admin' => $isAdmin,
            'invitation_token' => hash('sha256', $token),
            'invitation_expires_at' => now()->addDays(7),
        ]);

        return [$user, $token];
    }
}
