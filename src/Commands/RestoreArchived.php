<?php

namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Crypt;
use N02srt\AutoArchive\Events\ModelRestored;

class RestoreArchived extends Command
{
    protected $signature = 'restore:archived
                        {model : The fully-qualified model class}
                        {id? : ID of a specific record}
                        {--dry-run : Show what would be restored without changing anything}';

    protected $description = 'Restore archived records back to primary DB';

    public function handle()
    {
        $modelClass = $this->argument('model');
        $id         = $this->argument('id');
        $dryRun     = $this->option('dry-run');

        if ($dryRun === false && config('auto-archive.readonly')) {
            $this->warn("Archive restore is disabled in read-only mode.");
            return;
        }

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} not found.");
            return;
        }

        $model = new $modelClass;
        $table = $model->getArchiveTable();
        $conn  = config('auto-archive.archive_connection');
        $query = DB::connection($conn)->table($table);

        if ($id) {
            $query->where('id', $id);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            $this->info('No archived records found.');
            return;
        }

        if ($dryRun) {
            $this->info("Found {$records->count()} record(s) that would be restored:");
            foreach ($records as $record) {
                $this->line("  - [{$record->id}] " . json_encode((array) $record));
            }
            return;
        }

        foreach ($records as $data) {
            $dataArray = (array) $data;
            $decrypted = [];

            foreach ($dataArray as $key => $value) {
                if (property_exists($model, 'archiveEncryptedColumns') &&
                    in_array($key, $model->archiveEncryptedColumns ?? [])) {
                    try {
                        $decrypted[$key] = Crypt::decryptString($value);
                    } catch (\Exception $e) {
                        logger()->warning("Failed to decrypt `{$key}` for {$modelClass} ID {$data->id}");
                        $decrypted[$key] = $value; // fallback
                    }
                } else {
                    $decrypted[$key] = $value;
                }
            }

            DB::table($model->getTable())->insert($decrypted);
            DB::connection($conn)->table($table)->where('id', $data->id)->delete();
            Event::dispatch(new ModelRestored($model->newFromBuilder($decrypted)));
        }

        $this->info("✅ Restored {$records->count()} record(s).");
    }
}
