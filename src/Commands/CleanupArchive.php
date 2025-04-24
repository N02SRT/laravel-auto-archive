<?php
namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CleanupArchive extends Command
{
    protected $signature = 'archive:cleanup';
    protected $description = 'Purge old archive records';

    public function handle()
    {
        $models = Config::get('auto-archive.models', []);
        $conn   = config('auto-archive.archive_connection');
        $maxAge = config('auto-archive.max_archive_age');
        $cutoff = now()->subDays($maxAge);
        $total = 0;

        foreach ($models as $model) {
            $instance = new $model;
            $table = $instance->getArchiveTable();
            $count = DB::connection($conn)
                ->table($table)
                ->where('archived_at', '<', $cutoff)
                ->delete();
            $this->info("Purged {$count} from {$table}");
            $total += $count;
        }

        $this->info("Total purged: {$total}");
    }
}