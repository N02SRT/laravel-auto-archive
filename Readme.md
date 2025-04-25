# Laravel Auto Archive

`n02srt/laravel-auto-archive` is a Laravel package that automatically moves or flags old Eloquent records into a dedicated **archive** database connection. This helps reduce load on your primary database and improves performance over time.

---

## Features

- **Automatic Archiving**  
  Move or flag old records based on age or a custom scope.
- **Archive Method**
    - `move` (default): physically moves/deletes the recordsâ€”no `archived_at` column needed.
    - `flag`: marks records with an `archived_at` timestamp (requires that column).
- **Batch Processing**  
  Archives records in chunks (`batch_size`) with optional delays (`pause_seconds`).
- **Dry-Run Preview**  
  Preview what would be archived without actually modifying data.
- **Per-Model Retention**  
  Use a static `$archiveAfterDays` or `getRetentionDays()` to control retention per model.
- **Custom Archive Scopes**  
  Define `scopeArchiveScope(Builder $query)` for more granular control.
- **Separate Archive Connection**  
  Archived data is stored on a separate database connection (`archive`).
- **Restore / Unarchive**  
  Restore archived records using `restore:archived`.
- **Auto-Cleanup**  
  Purge old records from the archive database beyond `max_archive_age`.
- **Event Hooks**  
  Fires `ModelArchived` and `ModelRestored` events for observability.
- **Installer Command**  
  `auto-archive:setup` publishes config, injects traits, and runs migrations.
- **Optional Dashboard**  
  Livewire widget for visualizing archive metrics.

---

## Requirements

- PHP 8.0+
- Laravel 8.83+, 9.x, or 10.x
- Doctrine DBAL 3.x
- MySQL or compatible database

---

## Installation

```bash
composer require n02srt/laravel-auto-archive
```

Ensure the PHP `zip` extension is enabled in your `php.ini`:

```ini
extension=zip
```

---

## Configuration

### Environment Variables

```dotenv
DB_ARCHIVE_CONNECTION=archive
ARCHIVE_DB_HOST=127.0.0.1
DB_ARCHIVE_PORT=3306
DB_ARCHIVE_DATABASE=archive
DB_ARCHIVE_USERNAME=archive_user
DB_ARCHIVE_PASSWORD=secret
```

### Database Configuration

```php
'connections' => [
    'archive' => [
        'driver'    => 'mysql',
        'host'      => env('DB_ARCHIVE_HOST'),
        'port'      => env('DB_ARCHIVE_PORT'),
        'database'  => env('DB_ARCHIVE_DATABASE'),
        'username'  => env('DB_ARCHIVE_USERNAME'),
        'password'  => env('DB_ARCHIVE_PASSWORD'),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'strict'    => true,
    ],
],
```

### Package Config

```bash
php artisan vendor:publish --provider="N02srt\AutoArchive\AutoArchiveServiceProvider" --tag=config
```

Edit `config/auto-archive.php` to adjust global settings.

---

## Quick Setup

```bash
php artisan auto-archive:setup App\Models\YourModel --days=120
php artisan archive:models --dry-run
php artisan archive:models
```

---

## Contributing

Contributions are welcome. Please submit a pull request or open an issue for suggestions or improvements.

---

## License

MIT © Steve Ash
