<?php

namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class SetupAutoArchive extends Command
{
    protected $signature = 'auto-archive:setup
                            {model : The full model class (e.g. App\\Models\\Agreement)}
                            {--days= : Retention period, in days (overrides default)}';

    protected $description = 'Publish config, inject trait, scaffold & run migrations for archiving.';

    public function handle(Filesystem $fs)
    {
        $modelClass = trim($this->argument('model'), '\\');
        $days       = $this->option('days');
        $modelPath  = app_path(str_replace('\\', '/', Str::after($modelClass, 'App\\')) . '.php');

        // 1) Publish package config
        $this->info('Publishing package config...');
        Artisan::call('vendor:publish', [
            '--provider' => "N02srt\\AutoArchive\\AutoArchiveServiceProvider",
            '--tag'      => 'config',
        ]);
        $this->line(Artisan::output());

        // 2) Inject Trait & optional $archiveAfterDays into the model
        if (! $fs->exists($modelPath)) {
            $this->error("Model file not found at {$modelPath}");
            return 1;
        }

        $contents = $fs->get($modelPath);

        // 2a) Ensure the trait import is present
        if (! Str::contains($contents, 'N02srt\\AutoArchive\\Traits\\AutoArchiveable')) {
            $contents = preg_replace(
                '/^(namespace\s+[^;]+;)/m',
                "$1\n\nuse N02srt\\AutoArchive\\Traits\\AutoArchiveable;",
                $contents,
                1
            );
        }

        // 2b) Always inject `use AutoArchiveable;` directly after the class brace
        $contents = preg_replace(
            '/class\s+' . class_basename($modelClass) . '[^{]*\{/',
            "$0\n    use AutoArchiveable;",
            $contents,
            1
        );

        // 2c) Insert $archiveAfterDays if requested
        if ($days && ! Str::contains($contents, 'archiveAfterDays')) {
            $contents = preg_replace(
                '/use\s+AutoArchiveable;\s*/',
                "use AutoArchiveable;\n\n    /**\n     * Days before archiving\n     */\n    protected static \$archiveAfterDays = {$days};\n",
                $contents,
                1
            );
        }

        $fs->put($modelPath, $contents);
        $this->info("Trait and retention days correctly injected into {$modelPath}");

        // 3) Register the model in config
        $configPath = config_path('auto-archive.php');
        $config     = $fs->get($configPath);
        if (! Str::contains($config, $modelClass)) {
            $config = preg_replace(
                '/(\'models\'\s*=>\s*\[)/',
                "$1\n        {$modelClass}::class,",
                $config
            );
            $fs->put($configPath, $config);
            $this->info("Registered {$modelClass} in config/auto-archive.php");
        }

        // 4) Scaffold the archive-table migration
        $this->info("Creating archive-table migration for {$modelClass}...");
        Artisan::call('make:archive-migration', [
            'model' => $modelClass,
            '--path'=> 'database/migrations/archive',
        ]);
        $this->line(Artisan::output());

        // 5) If in “flag” method, scaffold archived_at column on primary table
        if (Config::get('auto-archive.method') === 'flag') {
            $table = Str::snake(Str::plural(class_basename($modelClass)));
            $this->info('Scaffolding archived_at column migration for primary table...');
            Artisan::call('make:migration', [
                'name'    => "add_archived_at_to_{$table}_table",
                '--table' => $table,
            ]);
            $this->line(Artisan::output());
        }

        // 6) Run archive migrations
        $this->info('Running migrations on archive connection...');
        Artisan::call('migrate', [
            '--path'     => 'database/migrations/archive',
            '--database' => Config::get('auto-archive.archive_connection'),
        ]);
        $this->line(Artisan::output());

        // 7) Run primary migrations if flag mode
        if (Config::get('auto-archive.method') === 'flag') {
            $this->info('Running primary migrations for flag mode...');
            Artisan::call('migrate');
            $this->line(Artisan::output());
        }

        $this->info("\n✅  Setup complete! You can now:\n   php artisan archive:models --dry-run");
        return 0;
    }
}
