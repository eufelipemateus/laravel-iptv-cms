<?php

return [
    'customer' => [
        'enabled' => (bool) env('MODULE_CUSTOMER_ENABLED', false),
    ],
    'vod' => [
        'enabled' => (bool) env('MODULE_VOD_ENABLED', false)
    ]
];
