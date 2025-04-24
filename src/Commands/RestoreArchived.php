<?php
namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use N02srt\AutoArchive\Events\ModelRestored;

class RestoreArchived extends Command
{
    protected $signature = 'restore:archived {model} {id?}';
    protected $description = 'Restore archived records back to primary DB';

    public function handle()
    {
        $model = $this->argument('model');
        $conn  = config('auto-archive.archive_connection');
        $instance = new $model;
        $table = $instance->getArchiveTable();
        $query = DB::connection($conn)->table($table);

        if ($this->argument('id')) {
            $query->where('id', $this->argument('id'));
        }

        $records = $query->get();
        foreach ($records as $row) {
            DB::table($instance->getTable())->insert((array) $row);
            DB::connection($conn)->table($table)->where('id', $row->id)->delete();
            Event::dispatch(new ModelRestored($model::find($row->id)));
        }

        $this->info('Restored ' . count($records) . ' record(s).');
    }
}