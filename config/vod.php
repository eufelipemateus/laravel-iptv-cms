<?php

return [
    'disk' => env('VOD_DISK', 'vod-master'),
    'max_upload_kilobytes' => env('VOD_MAX_UPLOAD_SIZE', 10485760),
    'allowed_video_mimetypes' => [
        'video/mp4',
        'video/x-matroska',
        'video/webm',
        'video/x-msvideo',
        'video/mpeg',
        'video/quicktime',
        'application/octet-stream',
    ],
];
