<?php

namespace N02srt\AutoArchive;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use N02srt\AutoArchive\Commands\ArchiveModels;
use N02srt\AutoArchive\Commands\MakeArchiveMigration;
use N02srt\AutoArchive\Commands\InstallAutoArchive;
use N02srt\AutoArchive\Commands\RestoreArchived;
use N02srt\AutoArchive\Commands\CleanupArchive;
use N02srt\AutoArchive\Commands\SetupAutoArchive;
use N02srt\AutoArchive\Events\ModelArchived;
use N02srt\AutoArchive\Events\ModelRestored;
use N02srt\AutoArchive\Listeners\SendArchiveNotifications;

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

        // Register event listeners
        Event::listen(ModelArchived::class, SendArchiveNotifications::class);
        Event::listen(ModelRestored::class, SendArchiveNotifications::class);

        if ($this->app->runningInConsole()) {
            // Register artisan commands
            $this->commands([
                ArchiveModels::class,
                MakeArchiveMigration::class,
                InstallAutoArchive::class,
                RestoreArchived::class,
                CleanupArchive::class,
                SetupAutoArchive::class,
            ]);

            // Schedule the archive task daily
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
