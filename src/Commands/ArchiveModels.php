<?php
namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ArchiveModels extends Command
{
    protected $signature = 'archive:models {--dry-run : Show count without moving}';
    protected $description = 'Archive records for configured models';

    public function handle()
    {
        $models = Config::get('auto-archive.models', []);
        foreach ($models as $model) {
            if (! class_exists($model)) {
                $this->error("Model {$model} not found.");
                continue;
            }
            $this->info("Processing {$model}...");
            $count = $model::archiveOld($this->option('dry-run'));
            $this->info($this->option('dry-run')
                ? "Would archive {$count} records."
                : "Archived {$count} records.");
        }
    }
}