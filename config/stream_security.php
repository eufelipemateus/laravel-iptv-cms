<?php

return [
    // Comma-separated schemes allowed for stream URLs.
    'allowed_schemes' => array_values(array_filter(array_map(
        static fn (string $scheme): string => strtolower(trim($scheme)),
        explode(',', (string) env('STREAM_ALLOWED_SCHEMES', 'https,http')),
    ))),

    // Port list for stream URLs. Empty list means any valid TCP port.
    'allowed_ports' => array_values(array_filter(array_map(
        static fn (string $port): int => (int) trim($port),
        explode(',', (string) env('STREAM_ALLOWED_PORTS', '80,443,1935')),
    ))),

    // Defensive upper bound to avoid pathological payload sizes.
    'max_url_length' => (int) env('STREAM_MAX_URL_LENGTH', 2048),

    'monitoring' => [
        'allowed_ports' => array_values(array_filter(array_map(
            static fn (string $port): int => (int) trim($port),
            explode(',', (string) env('STREAM_MONITOR_ALLOWED_PORTS', '80,443,1935')),
        ))),
        'block_localhost' => (bool) env('STREAM_MONITOR_BLOCK_LOCALHOST', true),
        'block_private_ips' => (bool) env('STREAM_MONITOR_BLOCK_PRIVATE_IPS', true),
        'block_cloud_metadata' => (bool) env('STREAM_MONITOR_BLOCK_CLOUD_METADATA', true),
        'max_redirects' => (int) env('STREAM_MONITOR_MAX_REDIRECTS', 3),
        'connect_timeout_seconds' => (float) env('STREAM_MONITOR_CONNECT_TIMEOUT', 5),
        'request_timeout_seconds' => (float) env('STREAM_MONITOR_REQUEST_TIMEOUT', 10),
        'max_response_bytes' => (int) env('STREAM_MONITOR_MAX_RESPONSE_BYTES', 1048576),
        'blocked_hostnames' => [
            'metadata.google.internal',
            'metadata',
            'instance-data.ec2.internal',
            '169.254.169.254.nip.io',
            '169.254.169.254.xip.io',
        ],
        'blocked_cidrs' => [
            '169.254.169.254/32',
            '100.100.100.200/32',
            '169.254.170.2/32',
        ],
    ],
];
