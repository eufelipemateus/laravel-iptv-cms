<?php

namespace App\Providers;

use App\Observers\AuditObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schema;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        if (app()->runningInConsole() && ! Schema::hasTable('audit_logs')) {
            return;
        }

        $this->registerAuditObservers();
    }

    private function registerAuditObservers(): void
    {
        foreach ($this->auditableModels() as $model) {
            $model::observe(AuditObserver::class);
        }
    }

    /** @return array<class-string<Model>> */
    private function auditableModels(): array
    {
        return config('audit.models', []);
    }
}
