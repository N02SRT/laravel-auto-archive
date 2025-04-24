<?php
namespace N02srt\AutoArchive;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use N02srt\AutoArchive\Commands\ArchiveModels;
use N02srt\AutoArchive\Commands\MakeArchiveMigration;
use N02srt\AutoArchive\Commands\InstallAutoArchive;
use N02srt\AutoArchive\Commands\RestoreArchived;
use N02srt\AutoArchive\Commands\CleanupArchive;
use N02srt\AutoArchive\Commands\SetupAutoArchive;

class AutoArchiveServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/auto-archive.php', 'auto-archive');
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/auto-archive.php' => config_path('auto-archive.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            // Register commands
            $this->commands([
                ArchiveModels::class,
                MakeArchiveMigration::class,
                InstallAutoArchive::class,
                RestoreArchived::class,
                CleanupArchive::class,
                SetupAutoArchive::class,
            ]);

            // Schedule archive:models daily
            $this->app->booted(function () {
                $this->app->make(Schedule::class)
                    ->command('archive:models')
                    ->dailyAt('02:00')
                    ->name('auto-archive:daily')
                    ->withoutOverlapping();
            });
        }
    }
}