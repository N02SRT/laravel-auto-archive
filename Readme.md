# Laravel Auto Archive

**`n02srt/laravel-auto-archive`** is a drop-in Laravel package that automates archiving of old Eloquent model data. It supports encrypted columns, separate archive databases, soft delete logic, queueing, dry-runs, and event notifications — all with easy setup and clean defaults.

---

## ✨ Features with Examples

- ✅ **Archive Methods** – `flag` (adds `archived_at`) or `move` to archive DB
- 📆 **Per-Model Retention** – Override with `$archiveAfterDays`
- 🔐 **Encrypted Columns** – Secure fields like SSNs or emails
- 🧼 **Selective Columns** – Archive only what you need
- 🔁 **Queue Support** – Use `--queue` to defer heavy jobs
- 🧪 **Dry-Run Support** – Test archive/restore without writing
- 📋 **Audit Logging** – Log each archived record (optional)
- 🛡 **Read-Only Mode** – Prevent mutations in prod
- ⏳ **Cleanup Command** – Delete archive records after X days
- 📣 **Notification Hooks** – Slack, email, or webhook triggers

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

## 🧬 Example Model Setup

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

## ⚙️ Config Options

> Published to: `config/auto-archive.php`

```php
'default_retention_days' => 30,                  // fallback if not set on model
'method'                => 'move',              // or 'flag'
'archive_connection'    => 'archive',           // DB connection for archive tables
'batch_size'            => 1000,                // rows per batch
'pause_seconds'         => 1,                   // delay between chunks
'max_archive_age'       => 365,                 // purge old archive rows
'bypass_soft_deletes'   => false,               // include soft-deleted records?
'readonly'              => false,               // prevent writes
'logging'               => ['enabled' => true], // log archive actions
'encryption' => [
    'enabled' => true,
    'key'     => env('AUTO_ARCHIVE_ENCRYPTION_KEY'),
],
'notifications' => [
    'slack' => env('AUTO_ARCHIVE_SLACK_WEBHOOK'),
    'email' => env('AUTO_ARCHIVE_NOTIFY_EMAIL'),
    'webhook' => env('AUTO_ARCHIVE_WEBHOOK_URL'),
],
'models' => [
    // App\Models\Invoice::class,
],
```

---

## 🧪 Artisan Commands

```bash
# Archive now
php artisan archive:models

# Dry run (no writes)
php artisan archive:models --dry-run

# Queue archive jobs
php artisan archive:models --queue

# Restore specific record
php artisan restore:archived App\Models\Invoice 42

# Preview restore
php artisan restore:archived App\Models\Invoice --dry-run

# Delete expired archive rows
php artisan archive:cleanup
```

---

## 📋 Archive Log (Optional)

If enabled (`logging.enabled`):

| model              | record_id | archived_at         |
|-------------------|-----------|----------------------|
| App\Models\User   | 52        | 2025-04-24 20:02:00  |

Define model in `src/Models/ArchiveLog.php` or use the one provided.

---

## 📣 Notifications

Enable Slack/email/webhook alerts when archiving/restoring:

```env
AUTO_ARCHIVE_NOTIFY_EMAIL=admin@example.com
AUTO_ARCHIVE_SLACK_WEBHOOK=https://hooks.slack.com/services/...
AUTO_ARCHIVE_WEBHOOK_URL=https://yourapp.com/webhook
```

Events:
- `ModelArchived`
- `ModelRestored`

---

## 🛡 Safety Features

- **Read-only mode:** Set `AUTO_ARCHIVE_READONLY=true`
- **Encryption:** Add fields to `$archiveEncryptedColumns`
- **Soft delete bypass:** Toggle in config

---

## 📄 License

MIT © Steve Ash  
This package exists so your tables can breathe. 🫁
