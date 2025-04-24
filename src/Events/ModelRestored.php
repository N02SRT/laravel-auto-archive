<?php
namespace N02srt\AutoArchive\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ModelRestored
{
    use Dispatchable, SerializesModels;

    public $model;
    public function __construct($model)
    {
        $this->model = $model;
    }
}
