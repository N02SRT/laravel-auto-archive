# Laravel Auto Archive

**`n02srt/laravel-auto-archive`** is a full-featured Laravel package for intelligently archiving Eloquent models based on retention policies. It supports encryption, restoration, soft delete bypassing, queue processing, and everything you'd want in a large-scale Laravel app that handles sensitive or aging data.

---

## ✨ Features with Examples

- ✅ Flag (`archived_at`) or fully move records to an archive table  
  _Example: Use `method => 'flag'` in config to keep records in place with a timestamp._
- 📆 Per-model retention periods with override support  
  _Example: `protected static $archiveAfterDays = 120;` in your model._
- 🧼 Selective column archiving (`$archiveColumns`)  
  _Example: `protected $archiveColumns = ['id', 'email', 'created_at'];`_
- 🔐 Column-level encryption on archive  
  _Example: `protected $archiveEncryptedColumns = ['email', 'ssn'];`_
- 🧪 Dry-run mode for both archive and restore  
  _Example: `php artisan archive:models --dry-run`_
- 💥 Supports soft delete bypassing (`deleted_at`)  
  _Example: Set `bypass_soft_deletes => true` in config._
- 🔁 Archive via Laravel Queues  
  _Example: `php artisan archive:models --queue`_
- 📋 Archive logs table for auditing  
  _Example: Archive actions logged in `archive_logs` table._
- 📣 Notification hooks  
  _Example: Events fire `ModelArchived` and `ModelRestored` you can listen to._
- 🔒 Read-only safety mode  
  _Example: Set `AUTO_ARCHIVE_READONLY=true` to block changes in prod._
- ⏳ Auto-cleanup of expired archive records  
  _Example: `php artisan archive:cleanup` purges based on `max_archive_age`_

---

## 🚀 Installation

```bash
composer require n02srt/laravel-auto-archive
```

Enable PHP Zip if necessary:

```ini
extension=zip
```

---

## ⚙️ Configuration

### 1. Publish Config

```bash
php artisan vendor:publish --provider="N02srt\AutoArchive\AutoArchiveServiceProvider" --tag=config
```

### 2. Define Archive DB in `.env`

```env
DB_ARCHIVE_CONNECTION=archive
DB_ARCHIVE_DATABASE=archive
DB_ARCHIVE_USERNAME=archive_user
DB_ARCHIVE_PASSWORD=secret
```

### 3. Define Archive DB in `config/database.php`

```php
'archive' => [
    'driver' => 'mysql',
    'host' => env('DB_ARCHIVE_HOST', '127.0.0.1'),
    'database' => env('DB_ARCHIVE_DATABASE'),
    'username' => env('DB_ARCHIVE_USERNAME'),
    'password' => env('DB_ARCHIVE_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
],
```

---

## 📦 Quick Setup

Automatically wires up everything:

```bash
php artisan auto-archive:setup App\Models\User --days=90
```

Creates the archive migration, updates your model, and runs migrations.

---

## 🧬 Example Model Setup

```php
use N02srt\AutoArchive\Traits\AutoArchiveable;

class User extends Model
{
    use AutoArchiveable;

    protected static $archiveAfterDays = 90;

    protected $archiveColumns = ['id', 'email', 'created_at'];
    protected $archiveEncryptedColumns = ['email'];

    public function scopeArchiveScope($query)
    {
        return $query->where('is_active', false);
    }
}
```

---

## 🧪 Artisan Command Examples

```bash
# Archive immediately
php artisan archive:models

# Preview what will be archived
php artisan archive:models --dry-run

# Push archive to queue
php artisan archive:models --queue

# Restore a single record
php artisan restore:archived App\Models\User 42

# Preview restore of all records
php artisan restore:archived App\Models\User --dry-run

# Cleanup expired archive records
php artisan archive:cleanup
```

---

## 📣 Notifications

```php
// Listen to archive events in your app or package
Event::listen(ModelArchived::class, function ($event) {
    Log::info("Archived: {$event->model->getTable()} #{$event->model->id}");
});
```

Or enable Slack/email/webhook notifications by setting `.env`:

```env
AUTO_ARCHIVE_NOTIFY_EMAIL=admin@example.com
AUTO_ARCHIVE_SLACK_WEBHOOK=https://hooks.slack.com/services/...
AUTO_ARCHIVE_WEBHOOK_URL=https://yourdomain.com/webhook
```

---

## 🛡 Security Features

| Setting                     | Purpose                                 |
|-----------------------------|-----------------------------------------|
| `readonly`                  | Prevents any changes (archive/restore) |
| `bypass_soft_deletes`       | Includes soft-deleted models in query  |
| `encryption`                | Enables Laravel encryption on fields   |

---

## 📋 Example Archive Log Entry

If enabled:

```php
ArchiveLog::create([
    'model' => App\Models\User::class,
    'record_id' => 42,
    'archived_at' => now(),
]);
```

---

## 📄 License

MIT © Steve Ash  
Made for large-scale Laravel projects that value data retention, performance, and peace of mind.
