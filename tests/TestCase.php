<?php

namespace Tests;

use App\Observers\AuditObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(RefreshDatabase::class, class_uses_recursive($this), true)) {
            return;
        }

        foreach (config('audit.models', []) as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
