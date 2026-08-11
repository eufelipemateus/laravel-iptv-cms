<?php

namespace Tests\Unit\Models;

use App\Models\IPTVConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IPTVConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_get_set_has_and_remove_configuration_values(): void
    {
        $this->assertFalse(IPTVConfig::has('CUSTOM_KEY'));

        $this->assertSame('first', IPTVConfig::add('CUSTOM_KEY', 'first'));
        $this->assertTrue(IPTVConfig::has('CUSTOM_KEY'));
        $this->assertSame('first', IPTVConfig::get('CUSTOM_KEY'));

        $this->assertSame('second', IPTVConfig::set('CUSTOM_KEY', 'second'));
        $this->assertSame('second', IPTVConfig::get('CUSTOM_KEY'));

        $this->assertSame(1, IPTVConfig::remove('CUSTOM_KEY'));
        $this->assertFalse(IPTVConfig::has('CUSTOM_KEY'));
    }

    public function test_casts_integer_boolean_defaults_and_false_strings_correctly(): void
    {
        IPTVConfig::set('INTEGER_VALUE', '42', 'integer');
        IPTVConfig::set('BOOLEAN_TRUE', 'true', 'bool');
        IPTVConfig::set('BOOLEAN_FALSE', 'false', 'bool');
        IPTVConfig::set('BOOLEAN_ZERO', '0', 'bool');

        $this->assertSame(42, IPTVConfig::get('INTEGER_VALUE'));
        $this->assertTrue(IPTVConfig::get('BOOLEAN_TRUE'));
        $this->assertFalse(IPTVConfig::get('BOOLEAN_FALSE'));
        $this->assertFalse(IPTVConfig::get('BOOLEAN_ZERO'));
        $this->assertSame('fallback', IPTVConfig::get('UNKNOWN_KEY', 'fallback'));
        $this->assertSame('br', IPTVConfig::getDefaultValueForField('CURRENT_LOCALE'));
    }

    public function test_defined_setting_metadata_is_available(): void
    {
        $rules = IPTVConfig::getValidationRules();

        $this->assertArrayHasKey('RADIO_STREAM', $rules);
        $this->assertSame('bool', IPTVConfig::getDataType('RADIO_STREAM'));
        $this->assertSame('string', IPTVConfig::getDataType('UNDEFINED_FIELD'));
        $this->assertIsArray(IPTVConfig::getAllBoleanSettings());
        $this->assertIsArray(IPTVConfig::getAllStringSettings());
    }
}
