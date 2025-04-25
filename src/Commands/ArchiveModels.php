<?php

namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use N02srt\AutoArchive\Jobs\ArchiveModelBatch;

class ArchiveModels extends Command
{
    protected $signature = 'archive:models
                            {--dry-run : Show count without moving}
                            {--queue : Dispatch archive jobs instead of running inline}';

    protected $description = 'Archive records for configured models';

    public function handle()
    {
        $models = Config::get('auto-archive.models', []);

        if (empty($models)) {
            $this->warn('No models defined in config/auto-archive.php');
            return;
        }

        foreach ($models as $model) {
            if (!class_exists($model)) {
                $this->error("Model {$model} not found.");
                continue;
            }

            if ($this->option('queue')) {
                ArchiveModelBatch::dispatch($model);
                $this->info("📤 Dispatched archive job for {$model}");
            } else {
                $this->info("Processing {$model}...");
                $count = $model::archiveOld($this->option('dry-run'));
                $this->info($this->option('dry-run')
                    ? "Would archive {$count} records."
                    : "Archived {$count} records.");
            }
        }
    }
}
