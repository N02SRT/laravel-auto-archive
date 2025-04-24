# Laravel Auto Archive 🗄️🚀

`n02srt/laravel-auto-archive` is a Laravel package that automatically moves or flags old Eloquent records into a dedicated **archive** database connection—keeping your primary database lean, mean, and screaming “who’s the fastest app in the west?” 🤠💨

---

## 🌟 Features

- **Automatic Archiving**  
  Move or flag old records based on age or a custom scope—set it and forget it!
- **Archive Method**
    - `move` (default): physically moves/deletes the dinosaurs—no `archived_at` column needed (they’re gone!).
    - `flag`: gently tags them with an `archived_at` timestamp so they know they’re retired (requires that column).
- **Batch Processing**  
  Chunk through records with `batch_size` and `pause_seconds` so your database doesn’t throw a tantrum.
- **Dry-Run Preview**  
  `--dry-run` says “I’m not touching anything, just tell me the gossip.”
- **Per-Model Retention**  
  Override retention days with a static property or a dynamic `getRetentionDays()` method—because one size never fits all.
- **Custom Archive Scopes**  
  Define `scopeArchiveScope(Builder $query)` on your model to archive by bizarre business rules (maybe “only archive unicorns”?).
- **Separate Archive Connection**  
  Your archived data lives safely on the `archive` connection—like a spa retreat for old rows.
- **Restore / Unarchive**  
  Oops, need them back? `restore:archived` to the rescue! 🦸
- **Auto-Cleanup**  
  Purge dusty archive records beyond `max_archive_age`—Marie Kondo your data.
- **Event Hooks**  
  Fires `ModelArchived` and `ModelRestored` events—hook Slack, email, smoke signals, whatever.
- **Installer Command**  
  `php artisan auto-archive:setup` does it all: publishes config, injects the trait, scaffolds & runs migrations—like magic (but real). ✨
- **Optional Dashboard**  
  Blade/Livewire widget to visualize archive stats—data never looked so good. 📊

---

## 🛠 Requirements

- PHP 8.0+
- Laravel 8.83+, 9.x or 10.x
- Doctrine DBAL 3.x
- MySQL (or compatible) for both primary and `archive` databases

---

## 🚀 Installation

1. **Get the package**
   ```bash
   composer require n02srt/laravel-auto-archive
   ```

2. **Enable PHP Zip extension** (because someone somewhere needs it)
   ```ini
   ; in your php.ini
   extension=zip
   ```

---

## ⚙️ Configuration

### 1. Environment Variables

Add these to your `.env` (defaults shown):

```dotenv
DB_ARCHIVE_CONNECTION=archive
ARCHIVE_DB_HOST=127.0.0.1
DB_ARCHIVE_PORT=3306
DB_ARCHIVE_DATABASE=archive
DB_ARCHIVE_USERNAME=archive_user
DB_ARCHIVE_PASSWORD=secret
```

### 2. Database Connections

In `config/database.php`, define the `archive` connection:

```php
'connections' => [

    // … other connections …

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

### 3. Package Config

Publish and inspect `config/auto-archive.php`:

```bash
php artisan vendor:publish   --provider="N02srt\AutoArchive\AutoArchiveServiceProvider"   --tag=config
```

Key settings in `config/auto-archive.php`:

```php
return [
    'default_retention_days' => 30,

    // Archive method: 'move' or 'flag'
    'method'                 => 'move',

    'archive_connection'     => env('DB_ARCHIVE_CONNECTION', 'archive'),

    'batch_size'             => 1000,
    'pause_seconds'          => 1,

    'max_archive_age'        => 365,

    'models'                 => [
        // App\Models\YourModel::class,
    ],
];
```

---

## 📦 Quick Setup (Two Commands)

1. **One-shot setup**
   ```bash
   php artisan auto-archive:setup App\Models\Agreement --days=120
   ```
2. **Dry-Run or Archive**
   ```bash
   php artisan archive:models --dry-run
   php artisan archive:models
   ```

---

## 🤝 Contributing

MIT © Steve Ash

> “I’m not saying this package will solve all your problems, but it will definitely solve your old-data headache.” 😄
