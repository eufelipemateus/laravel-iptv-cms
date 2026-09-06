<?php

namespace App\Providers;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\Customer;
use App\Models\CustomerCdn;
use App\Models\CustomerInvoce;
use App\Models\CustomerPlan;
use App\Models\IPTVConfig;
use App\Models\IPTVTaxVat;
use App\Models\IPTVVodVideo;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        foreach ([
            Channel::class, ChannelCdn::class, ChannelGroup::class, ChannelUrl::class,
            Customer::class, CustomerCdn::class, CustomerInvoce::class, CustomerPlan::class,
            IPTVConfig::class, IPTVTaxVat::class, IPTVVodVideo::class, User::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
