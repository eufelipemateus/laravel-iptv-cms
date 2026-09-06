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
        $items = json_decode((string) file_get_contents(resource_path('menu.json')), true, flags: JSON_THROW_ON_ERROR);
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        config(['modules.epg.enabled' => true]);
        $this->actingAs($admin);
        $this->assertStringContainsString('EPG', $this->render($items));
        $this->actingAs($user);
        $this->assertStringNotContainsString('EPG', $this->render($items));
        config(['modules.epg.enabled' => false]);
        $this->actingAs($admin);
        $this->assertStringNotContainsString('EPG', $this->render($items));
    }

    private function render(array $items): string
    {
        $menu = new Menu;
        $menu->add($items);

        return $menu->view()->render();
    }
}
