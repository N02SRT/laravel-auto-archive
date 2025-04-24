<?php

return [
    'default_retention_days' => 30,
    'method'                => 'move',
    'archive_connection'    => env('DB_ARCHIVE_CONNECTION', 'archive'),
    'batch_size'            => 1000,
    'pause_seconds'         => 1,
    'max_archive_age'       => 365,
    'models'                => [
        // App\\Models\\Invoice::class,
    ],
];