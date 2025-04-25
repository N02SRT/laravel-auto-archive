<?php

return [
    'default_retention_days' => 30,
    'method'                => 'move',
    'archive_connection'    => env('DB_ARCHIVE_CONNECTION', 'archive'),
    'batch_size'            => 1000,
    'pause_seconds'         => 1,
    'max_archive_age'       => 365,
    'readonly'              => env('AUTO_ARCHIVE_READONLY', false),
    'notifications'         => [
        'slack'   => env('AUTO_ARCHIVE_SLACK_WEBHOOK'),
        'email'   => env('AUTO_ARCHIVE_NOTIFY_EMAIL'),
        'webhook' => env('AUTO_ARCHIVE_WEBHOOK_URL'),
    ],
    'encryption'            => [
        'enabled' => true,
        'key'     => env('AUTO_ARCHIVE_ENCRYPTION_KEY'), // 32 bytes base64 or raw
    ],
    'models'                => [
        // App\\Models\\Invoice::class,
    ],
];