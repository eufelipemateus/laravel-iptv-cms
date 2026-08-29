<?php

return [
    'customer' => [
        'enabled' => (bool) env('MODULE_CUSTOMER_ENABLED', false),
    ],
    'vod' => [
        'enabled' => (bool) env('MODULE_VOD_ENABLED', false),
    ],
    'epg' => [
        'enabled' => (bool) env('MODULE_EPG_ENABLED', true),
        'request_timeout' => (int) env('EPG_REQUEST_TIMEOUT', 15),
        'connect_timeout' => (int) env('EPG_CONNECT_TIMEOUT', 5),
        'max_download_bytes' => (int) env('EPG_MAX_DOWNLOAD_BYTES', 52428800),
        'max_uncompressed_bytes' => (int) env('EPG_MAX_UNCOMPRESSED_BYTES', 52428800),
        'max_programmes_per_import' => (int) env('EPG_MAX_PROGRAMMES_PER_IMPORT', 500000),
        'retention_days' => (int) env('EPG_RETENTION_DAYS', 7),
        'default_timezone' => env('EPG_DEFAULT_TIMEZONE', 'UTC'),
        'max_redirects' => (int) env('EPG_MAX_REDIRECTS', 3),
        'sync_lock_seconds' => (int) env('EPG_SYNC_LOCK_SECONDS', 1800),
    ],
];
