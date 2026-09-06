<?php

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\Customer;
use App\Models\CustomerCdn;
use App\Models\CustomerPlan;
use App\Models\IPTVConfig;
use App\Models\IPTVTaxVat;
use App\Models\IPTVVodVideo;
use App\Models\User;

return [
    'models' => [
        Channel::class,
        ChannelCdn::class,
        ChannelGroup::class,
        ChannelUrl::class,
        Customer::class,
        CustomerCdn::class,
        CustomerPlan::class,
        IPTVConfig::class,
        IPTVTaxVat::class,
        IPTVVodVideo::class,
        User::class,
    ],

    'restorable_models' => [
        Channel::class,
        ChannelCdn::class,
        ChannelGroup::class,
        ChannelUrl::class,
        Customer::class,
        CustomerCdn::class,
        CustomerPlan::class,
        IPTVConfig::class,
        IPTVTaxVat::class,
    ],

    'metadata' => [
        'max_url_length' => env('AUDIT_MAX_URL_LENGTH', 2048),
        'max_user_agent_length' => env('AUDIT_MAX_USER_AGENT_LENGTH', 1024),
    ],

    'hidden_attributes' => [
        'password',
        'remember_token',
        'invitation_token',
        'api_token',
        'access_token',
        'auth_token_hash',
    ],
];
