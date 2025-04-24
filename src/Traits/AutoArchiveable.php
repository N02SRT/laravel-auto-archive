<?php
namespace N02srt\AutoArchive\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use N02srt\AutoArchive\Events\ModelArchived;
use N02srt\AutoArchive\Events\ModelRestored;

trait AutoArchiveable
{
    public static function bootAutoArchiveable()
    {
        // Only apply the global "not_archived" scope if we're _flagging_ records.
        if (config('auto-archive.method') === 'flag') {
            static::addGlobalScope('not_archived', function (Builder $builder) {
                $model = $builder->getModel();
                $builder->whereNull($model->getQualifiedArchivedAtColumn());
            });
        }
    }

    public static function getRetentionDays(): int
    {
        return static::$archiveAfterDays
            ?? config('auto-archive.default_retention_days');
    }

    public function getArchiveScope(Builder $query): Builder
    {
        if (method_exists($this, 'scopeArchiveScope')) {
            return $this->scopeArchiveScope($query);
        }
        return $query->where('created_at', '<', Carbon::now()->subDays(static::getRetentionDays()));
    }

    public function getArchivedAtColumn(): string
    {
        return $this->archiveAtColumn ?? 'archived_at';
    }

    public function getQualifiedArchivedAtColumn(): string
    {
        return $this->getTable() . '.' . $this->getArchivedAtColumn();
    }

    public static function archiveOld(bool $dryRun = false): int
    {
        $batch = config('auto-archive.batch_size');
        $pause = config('auto-archive.pause_seconds');
        $conn   = config('auto-archive.archive_connection');
        $count  = 0;

        static::withoutGlobalScopes()->getModel(); // ensure model
        $query = (new static)->getArchiveScope(static::query());

        $total = $query->count();
        if ($dryRun) {
            return $total;
        }

        $query->chunk($batch, function ($models) use (&$count, $conn, $pause) {
            DB::transaction(function () use ($models, $conn, &$count) {
                foreach ($models as $model) {
                    DB::connection($conn)
                        ->table($model->getArchiveTable())
                        ->insert($model->getAttributes());
                    $model->delete();
                    Event::dispatch(new ModelArchived($model));
                    $count++;
                }
            });
            sleep($pause);
        });

        return $count;
    }

    public function getArchiveTable(): string
    {
        return $this->archiveTable ?? ($this->getTable() . '_archives');
    }

    public function scopeWithArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_archived');
    }

    public function scopeOnlyArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_archived')
            ->whereNotNull($this->getArchivedAtColumn());
    }
}