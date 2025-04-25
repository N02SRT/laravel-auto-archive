<?php

namespace N02srt\AutoArchive\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveModelBatch implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected string $modelClass;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!class_exists($this->modelClass)) {
            return;
        }

        $model = new $this->modelClass;

        if (!method_exists($model, 'archiveOld')) {
            return;
        }

        $this->modelClass::archiveOld(false);
    }
}
