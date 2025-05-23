<?php

namespace N02srt\AutoArchive\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use N02srt\AutoArchive\Events\ModelArchived;
use N02srt\AutoArchive\Models\ArchiveLog;

trait AutoArchiveable
{
    public static function bootAutoArchiveable()
    {
        if (config('auto-archive.method') === 'flag') {
            static::addGlobalScope('not_archived', function (Builder $builder) {
                $model = $builder->getModel();
                $builder->whereNull($model->getQualifiedArchivedAtColumn());
            });
        }
    }

    public static function getRetentionDays(): int
    {
        return static::$archiveAfterDays ?? config('auto-archive.default_retention_days');
    }

    public function getArchiveScope(Builder $query): Builder
    {
        $query->where('created_at', '<', Carbon::now()->subDays(static::getRetentionDays()));

        if (method_exists($this, 'scopeArchiveScope')) {
            $query = $this->scopeArchiveScope($query);
        }

        if (
            method_exists($this, 'getDeletedAtColumn') &&
            ! config('auto-archive.bypass_soft_deletes', false)
        ) {
            $query->whereNull($this->getQualifiedDeletedAtColumn());
        }

        return $query;
    }

    public function getArchivedAtColumn(): string
    {
        return $this->archiveAtColumn ?? 'archived_at';
    }

    public function getQualifiedArchivedAtColumn(): string
    {
        return $this->getTable() . '.' . $this->getArchivedAtColumn();
    }

    public function getQualifiedDeletedAtColumn(): string
    {
        return $this->getTable() . '.' . $this->getDeletedAtColumn();
    }

    public static function archiveOld(bool $dryRun = false): int
    {
        if (config('auto-archive.readonly')) {
            logger()->warning("AutoArchive is in read-only mode. No records will be archived.");
            return 0;
        }

        $batch = config('auto-archive.batch_size');
        $pause = config('auto-archive.pause_seconds');
        $conn  = config('auto-archive.archive_connection');
        $count = 0;

        static::withoutGlobalScopes()->getModel();
        $query = (new static)->getArchiveScope(static::query());

        $total = $query->count();
        if ($dryRun) {
            return $total;
        }

        $query->chunk($batch, function ($models) use (&$count, $conn, $pause) {
            DB::transaction(function () use ($models, $conn, &$count) {
                foreach ($models as $model) {
                    $attributes = $model->getAttributes();

                    if (property_exists($model, 'archiveColumns') && is_array($model->archiveColumns)) {
                        $attributes = array_intersect_key($attributes, array_flip($model->archiveColumns));
                    }

                    DB::connection($conn)
                        ->table($model->getArchiveTable())
                        ->insert($attributes);

                    if (config('auto-archive.method') === 'flag') {
                        $model->update([$model->getArchivedAtColumn() => now()]);
                    } elseif (config('auto-archive.method') === 'move') {
                        if (config('auto-archive.hard_delete')) {
                            DB::table($model->getTable())->where('id', $model->getKey())->delete();
                        } else {
                            $model->delete();
                        }
                    } elseif (config('auto-archive.method') === 'mirror') {
                        // Do nothing after copy
                    }

                    if (config('auto-archive.logging.enabled')) {
                        ArchiveLog::create([
                            'model' => get_class($model),
                            'record_id' => $model->getKey(),
                            'archived_at' => now(),
                        ]);
                    }

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
