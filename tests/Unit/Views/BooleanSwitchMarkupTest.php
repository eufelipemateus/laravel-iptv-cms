<?php

namespace Tests\Unit\Views;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Tests\TestCase;

class BooleanSwitchMarkupTest extends TestCase
{
    public function test_all_visible_blade_checkboxes_use_bootstrap_four_switch_markup(): void
    {
        $files = new RegexIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))),
            '/\.blade\.php$/',
        );

        $checkboxCount = 0;

        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());
            preg_match_all('/<input\b[^>]*type=["\']checkbox["\'][^>]*>/i', $contents, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as [$input, $offset]) {
                $checkboxCount++;
                $context = substr($contents, max(0, $offset - 500), strlen($input) + 1000);

                $this->assertStringContainsString('class="custom-control-input"', $input, $file->getPathname());
                $this->assertStringContainsString('custom-control custom-switch', $context, $file->getPathname());
                $this->assertStringContainsString('custom-control-label', $context, $file->getPathname());
            }
        }

        $this->assertGreaterThan(0, $checkboxCount);
    }
}
