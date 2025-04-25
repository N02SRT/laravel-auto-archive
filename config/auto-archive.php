<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Retention Period
    |--------------------------------------------------------------------------
    | The default number of days before a record is eligible for archiving.
    | Can be overridden per model using `$archiveAfterDays`.
    */
    'default_retention_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Archiving Method
    |--------------------------------------------------------------------------
    | 'move'  - physically moves records to archive tables
    | 'flag'  - sets `archived_at` timestamp and keeps in primary table
    */
    'method' => 'move',

    /*
    |--------------------------------------------------------------------------
    | Archive Database Connection
    |--------------------------------------------------------------------------
    | The database connection to use for archived records.
    | Define this connection in your `config/database.php`.
    */
    'archive_connection' => env('DB_ARCHIVE_CONNECTION', 'archive'),

    /*
    |--------------------------------------------------------------------------
    | Batch Size & Delay
    |--------------------------------------------------------------------------
    | Control how many records are archived per chunk, and how long to wait
    | between chunks (in seconds). Helps reduce load on busy systems.
    */
    'batch_size' => 1000,
    'pause_seconds' => 1,

    /*
    |--------------------------------------------------------------------------
    | Maximum Archive Age (for Cleanup)
    |--------------------------------------------------------------------------
    | Records older than this (in days) will be deleted from archive tables
    | when running `php artisan archive:cleanup`.
    */
    'max_archive_age' => 365,

    /*
    |--------------------------------------------------------------------------
    | Soft Delete Handling
    |--------------------------------------------------------------------------
    | If true, will include soft-deleted records in the archive scope.
    */
    'bypass_soft_deletes' => false,

    /*
    |--------------------------------------------------------------------------
    | Read-Only Mode
    |--------------------------------------------------------------------------
    | Prevents archiving or restoring actions (no DB changes). Useful for
    | production environments or maintenance windows.
    */
    'readonly' => env('AUTO_ARCHIVE_READONLY', false),

    /*
     * --------------------------------------------------------------------------
     * Hard Delete After Archiving
     * --------------------------------------------------------------------------
     * If true, records will be permanently deleted after archiving.
     * This is useful for compliance or space-saving.
     * If false, records will be moved to archive tables.
     * This setting is only relevant if 'method' is set to 'move'.
     */
    'hard_delete' => false, // If true, records will be fully removed after archiving

    /*
    |--------------------------------------------------------------------------
    | Archive Logging (Audit Trail)
    |--------------------------------------------------------------------------
    | If enabled, writes an entry to `archive_logs` table for each archived
    | record.
    */
    'logging' => [
        'enabled' => env('AUTO_ARCHIVE_LOGGING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications (Optional)
    |--------------------------------------------------------------------------
    | Send alerts or payloads when records are archived or restored.
    | Hook into Slack, email, or your own webhook.
    */
    'notifications' => [
        'slack'   => env('AUTO_ARCHIVE_SLACK_WEBHOOK'),
        'email'   => env('AUTO_ARCHIVE_NOTIFY_EMAIL'),
        'webhook' => env('AUTO_ARCHIVE_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    | Encrypt specific fields before moving them to archive tables. Use
    | Laravel's Crypt system. Only affects fields listed in `$archiveEncryptedColumns`.
    */
    'encryption' => [
        'enabled' => true,
        'key'     => env('AUTO_ARCHIVE_ENCRYPTION_KEY'), // 32 bytes (base64 or raw)
    ],

    /*
    |--------------------------------------------------------------------------
    | Models to Archive
    |--------------------------------------------------------------------------
    | List any models you want the system to automatically archive here.
    | These will be processed when `php artisan archive:models` runs.
    */
    'models' => [
        // App\\Models\\Invoice::class,
    ],
];
