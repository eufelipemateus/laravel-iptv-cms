<?php

namespace Tests\Feature\Middleware;

use App\Models\IPTVConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicCdnAndLocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_cdn_middleware_allows_enabled_cdn_and_blocks_false_strings(): void
    {
        Route::middleware('public_cdn')->get('/_testing/public-cdn', fn () => response('ok'));

        IPTVConfig::set('URL_CDN', true, 'bool');
        $this->get('/_testing/public-cdn')->assertOk()->assertSee('ok');

        IPTVConfig::set('URL_CDN', 'false', 'bool');
        $this->get('/_testing/public-cdn')->assertStatus(503)->assertSee('Url cdn is disabled.');
    }

    public function test_locale_middleware_sets_configured_locale_and_falls_back_when_invalid(): void
    {
        Route::middleware('iptv_locale')->get('/_testing/locale', fn () => response(App::getLocale()));

        IPTVConfig::set('CURRENT_LOCALE', 'br', 'locale');
        $this->get('/_testing/locale')->assertOk()->assertSee('br');

        IPTVConfig::set('CURRENT_LOCALE', 'invalid-locale', 'locale');
        $this->get('/_testing/locale')->assertOk()->assertSee(config('app.fallback_locale'));
    }
}
