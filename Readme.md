# Laravel Auto Archive

**`n02srt/laravel-auto-archive`** is a full-featured Laravel package for intelligently archiving Eloquent models based on retention policies. It supports encryption, restoration, soft delete bypassing, queue processing, and everything you'd want in a large-scale Laravel app that handles sensitive or aging data.

---

## ✨ Features

- ✅ Flag (`archived_at`) or fully move records to an archive table
- 📆 Per-model retention periods with override support
- 🧼 Selective column archiving (`$archiveColumns`)
- 🔐 Column-level encryption on archive, with decryption on restore
- 🧪 Dry-run mode for both archive and restore
- 💥 Supports soft delete bypassing (`deleted_at`)
- 🔁 Archive via Laravel Queues (w/ retries + Horizon support)
- 📋 Archive logs table for auditing
- 📣 Notification hooks (Slack, email, webhook via `ModelArchived`, `ModelRestored`)
- 🔒 Read-only safety mode to protect production data
- ⏳ Auto-cleanup of expired archive records
- ⚙️ Smart setup CLI: injects traits, builds archive tables, publishes config

---

## 🚀 Installation

```bash
composer require n02srt/laravel-auto-archive
```

Enable the PHP `zip` extension if needed (for config publishing):

```ini
extension=zip
```

---

## ⚙️ Configuration

### 1. Publish Config

```bash
php artisan vendor:publish --provider="N02srt\AutoArchive\AutoArchiveServiceProvider" --tag=config
```

### 2. Set `.env` archive DB connection

```env
DB_ARCHIVE_CONNECTION=archive
DB_ARCHIVE_DATABASE=archive
DB_ARCHIVE_USERNAME=archive_user
DB_ARCHIVE_PASSWORD=secret
```

And define it in `config/database.php`.

---

## 📦 Quick Setup

Automatically publishes config, injects trait, sets retention, and builds migration:

```bash
php artisan auto-archive:setup App\Models\YourModel --days=90
```

---

## 🛠 Model Setup Example

```php
use N02srt\AutoArchive\Traits\AutoArchiveable;

class Agreement extends Model
{
    use AutoArchiveable;

    protected static $archiveAfterDays = 120;

    protected $archiveColumns = ['id', 'customer_id', 'created_at'];
    protected $archiveEncryptedColumns = ['email', 'ssn'];

    public function scopeArchiveScope($query)
    {
        return $query->where('status', 'completed');
    }
}
```

---

## 🧪 Artisan Commands

| Command                             | Description                                  |
|------------------------------------|----------------------------------------------|
| `archive:models`                   | Archive records for configured models        |
| `archive:models --dry-run`         | Preview what would be archived               |
| `archive:models --queue`           | Queue archive jobs instead of inline         |
| `restore:archived`                 | Restore from archive back to main DB         |
| `restore:archived --dry-run`       | Preview restore without modifying anything   |
| `archive:cleanup`                  | Delete expired archive records               |
| `auto-archive:setup`               | Auto-configure model with trait, migration   |
| `make:archive-migration`           | Generate archive table for a model           |

---

## 📣 Notifications

- Slack message via webhook
- Email alert to admin
- Webhook to 3rd-party service

Triggered via events:

- `ModelArchived`
- `ModelRestored`

---

## 🔒 Security Options

- `readonly`: Prevents any data from being moved/restored
- `bypass_soft_deletes`: Includes soft-deleted models in archive scope
- `encryption`: Encrypt specific fields before archiving

---

## 📋 Archive Logs (Optional)

Enable archive logging for auditing:

```php
use App\Models\ArchiveLog;

ArchiveLog::create([
    'model' => get_class($model),
    'record_id' => $model->getKey(),
]);
```

---

## ✅ Example `.env` Variables

```env
AUTO_ARCHIVE_READONLY=false
AUTO_ARCHIVE_ENCRYPTION_KEY=base64:your_32_byte_base64_key
AUTO_ARCHIVE_NOTIFY_EMAIL=admin@example.com
AUTO_ARCHIVE_SLACK_WEBHOOK=https://hooks.slack.com/...
AUTO_ARCHIVE_WEBHOOK_URL=https://yourdomain.com/webhook
```

---

## 📄 License

MIT © Steve Ash  
PRs welcome, logs respected, retention honored.

