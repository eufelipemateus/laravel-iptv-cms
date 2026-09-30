<?php

namespace Tests\Feature;

use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class VodCreateViewTest extends TestCase
{
    public function test_create_view_only_shows_the_essential_first_step(): void
    {
        $view = $this->view('vod', [
            'errors' => new ViewErrorBag,
        ]);

        $view->assertSee('name="name"', false);
        $view->assertSee('name="description"', false);
        $view->assertSee('name="file"', false);
        $view->assertDontSee('name="type"', false);
        $view->assertDontSee('name="poster"', false);
        $view->assertDontSee('name="original_locale"', false);
        $view->assertDontSee('translation_locale_picker');
        $view->assertDontSee('name="status"', false);
        $view->assertDontSee('name="genres"', false);
        $view->assertDontSee('name="published_at"', false);
        $view->assertDontSee('name="available_from"', false);
        $view->assertDontSee('name="available_until"', false);
        $view->assertDontSee('Processing');
        $view->assertDontSee('Technical data');
    }
}
