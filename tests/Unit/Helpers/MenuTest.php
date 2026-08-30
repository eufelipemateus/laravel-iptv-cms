<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_epg_menu_requires_enabled_module_and_admin_user(): void
    {
        $item = ['title' => 'EPG', 'enabled_when' => ['modules.epg.enabled', 'auth.user.is_admin'], 'menus' => []];
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        config(['modules.epg.enabled' => true]);
        $this->actingAs($admin);
        $this->assertStringContainsString('EPG', $this->render($item));
        $this->actingAs($user);
        $this->assertStringNotContainsString('EPG', $this->render($item));
        config(['modules.epg.enabled' => false]);
        $this->actingAs($admin);
        $this->assertStringNotContainsString('EPG', $this->render($item));
    }

    private function render(array $item): string
    {
        $menu = new Menu;
        $menu->add($item);

        return $menu->view()->render();
    }
}
