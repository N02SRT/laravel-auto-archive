<?php

namespace N02srt\AutoArchive\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model',
        'record_id',
        'archived_at',
    ];
}
