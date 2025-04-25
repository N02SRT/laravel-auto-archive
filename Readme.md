# Laravel Auto Archive

**`n02srt/laravel-auto-archive`** is a drop-in Laravel package that automates archiving of old Eloquent model data. It supports encrypted columns, separate archive databases, soft delete logic, queueing, dry-runs, and event notifications — all with easy setup and clean defaults.

---

## 🚀 Installation

```bash
composer require n02srt/laravel-auto-archive
```

Then run:

```bash
php artisan auto-archive:setup App\Models\Invoice --days=90
```

✔️ This publishes config, injects the trait, registers your model, builds migrations, and runs them.

---

## ✨ Features

### 🧠 Archive Methods

Choose between two strategies:
- `move`: Move records to a separate archive table/database
- `flag`: Add a timestamp to the `archived_at` column and keep the record in place

```php
'method' => 'move' // or 'flag'
```

---

### 📆 Per-Model Retention

Override archive timing for specific models using:

```php
protected static $archiveAfterDays = 90;
```

---

### 🧼 Selective Column Archiving

Archive only the fields you need:

```php
protected $archiveColumns = ['id', 'amount', 'customer_id'];
```

---

### 🔐 Encrypted Columns

Secure sensitive fields during archival:

```php
protected $archiveEncryptedColumns = ['ssn', 'email'];
```

Encryption uses Laravel’s `Crypt` service.

---

### 💣 Hard Delete After Archive

When enabled, archived records are fully removed from the source database (not soft-deleted).

```php
'hard_delete' => true
```

Use with caution in production!

---

### 🔁 Queue Support

Archive in the background with:

```bash
php artisan archive:models --queue
```

Supports Laravel Horizon and retry/backoff settings.

---

### 🧪 Dry-Run Preview

Preview what would be archived or restored without making changes:

```bash
php artisan archive:models --dry-run
php artisan restore:archived App\Models\Invoice --dry-run
```

---

### 📋 Archive Logs (Optional)

Enable audit logging:

```php
'logging' => ['enabled' => true]
```

Archived records are written to a central `archive_logs` table.

---

### 📣 Notification Hooks

Notify external systems when models are archived or restored:

```env
AUTO_ARCHIVE_NOTIFY_EMAIL=admin@example.com
AUTO_ARCHIVE_SLACK_WEBHOOK=https://hooks.slack.com/services/...
AUTO_ARCHIVE_WEBHOOK_URL=https://yourapp.com/webhook
```

Available events:
- `ModelArchived`
- `ModelRestored`

---

### 🛡 Safety Features

- **Read-only mode:** Set `AUTO_ARCHIVE_READONLY=true` to block all operations
- **Soft delete bypass:** Use `bypass_soft_deletes => true` to include soft-deleted records
- **Max archive age:** Automatically purge stale archive data after N days

---

## ⚙️ Config File (Published to `config/auto-archive.php`)

```php
'default_retention_days' => 30,
'method'                => 'move',
'archive_connection'    => 'archive',
'batch_size'            => 1000,
'pause_seconds'         => 1,
'max_archive_age'       => 365,
'bypass_soft_deletes'   => false,
'readonly'              => env('AUTO_ARCHIVE_READONLY', false),
'hard_delete'           => false,
'logging' => [
    'enabled' => env('AUTO_ARCHIVE_LOGGING_ENABLED', true),
],
'notifications' => [
    'slack' => env('AUTO_ARCHIVE_SLACK_WEBHOOK'),
    'email' => env('AUTO_ARCHIVE_NOTIFY_EMAIL'),
    'webhook' => env('AUTO_ARCHIVE_WEBHOOK_URL'),
],
'encryption' => [
    'enabled' => true,
    'key'     => env('AUTO_ARCHIVE_ENCRYPTION_KEY'),
],
'models' => [
    // App\Models\Invoice::class,
],
```

---

## 🧪 Artisan Command Summary

```bash
php artisan archive:models                  # Run archiving
php artisan archive:models --dry-run        # Preview without changes
php artisan archive:models --queue          # Queue archiving
php artisan restore:archived App\Model 42  # Restore single record
php artisan archive:cleanup                 # Delete expired archive records
```

---

## 🧬 Example Model

```php
use N02srt\AutoArchive\Traits\AutoArchiveable;

class Invoice extends Model
{
    use AutoArchiveable;

    protected static $archiveAfterDays = 90;
    protected $archiveColumns = ['id', 'amount', 'customer_id'];
    protected $archiveEncryptedColumns = ['amount'];

    public function scopeArchiveScope($query)
    {
        return $query->where('status', 'paid');
    }
}
```

---

## 📄 License

MIT © Steve Ash  
Your database just got leaner. 🧹


---

## ⚠️ Disclaimer

This package includes functionality that can permanently **delete records** from your database.  
If you enable features like `hard_delete`, records will be irreversibly removed from the primary database.

Please make sure you:
- Understand what each configuration setting does
- Test in staging or development environments first
- Keep backups of your data

> **I am not responsible for any data loss, misconfiguration, or unintended consequences from using this package. Use at your own risk.**

---
