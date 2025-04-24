<?php
namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class InstallAutoArchive extends Command
{
    protected $signature = 'auto-archive:install';
    protected $description = 'Publish config and generate migrations';

    public function handle()
    {
        // Publish config
        $this->info('Publishing config...');
        Artisan::call('vendor:publish', [
            '--provider' => "N02srt\\AutoArchive\\AutoArchiveServiceProvider",
            '--tag'      => 'config',
        ]);
        $this->line(Artisan::output());

        $models = Config::get('auto-archive.models', []);
        if (empty($models)) {
            return $this->warn('No models defined.');
        }
        foreach ($models as $model) {
            $this->info("Generating migration for {$model}...");
            Artisan::call('make:archive-migration', ['model' => $model]);
            $this->line(Artisan::output());
        }
        $this->info('Done. Run migrate --database=' . config('auto-archive.archive_connection'));
    }
}