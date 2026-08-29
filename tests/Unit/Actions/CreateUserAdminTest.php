<?php

namespace Tests\Unit\Actions;

use App\Actions\Users\CreateUserAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_active_administrator_with_a_hashed_password(): void
    {
        $user = CreateUserAdmin::run([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);

        $this->assertSame('Administrator', $user->name);
        $this->assertSame('admin@example.test', $user->email);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->active);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertNotSame('secret-password', $user->password);
    }
}
