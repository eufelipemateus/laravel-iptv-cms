<?php

return [
    [
        'elements' => [
            [
                'name' => 'RADIO_STREAM',
                'rules' => 'boolean',
                'data' => 'bool',
                'value' => true,
            ],
            [
                'name' => 'DOWNLOAD_FILE',
                'rules' => 'boolean',
                'data' => 'bool',
                'value' => false,
            ],
            [
                'name' => 'URL_CDN',
                'rules' => 'boolean',
                'data' => 'bool',
                'value' => false,
            ],
            [
                'name' => 'CURRENT_LOCALE',
                'rules' => 'string',
                'data' => 'locale',
                'value' => 'br',
            ],
            [
                'name' => 'mode',
                'rules' => 'required|string|in:m3u8,dtv3',
                'data' => 'string',
                'value' => 'm3u8',
            ],
            [
                'name' => 'BSINESS_NAME',
                'rules' => 'string',
                'data' => 'string',
                'value' => 'Acme Corporation',
            ],
        ],
    ],
];
