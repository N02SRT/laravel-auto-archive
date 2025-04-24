<?php
namespace N02srt\AutoArchive\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class MakeArchiveMigration extends Command
{
    protected $signature = 'make:archive-migration
        {model : The model class}
        {--path= : Migration path (default database/migrations/archive)}';
    protected $description = 'Generate migration for archive table';

    public function handle(Filesystem $fs)
    {
        $modelClass = $this->argument('model');
        $model = new $modelClass;
        $table = $model->getTable();
        $archive = $model->getArchiveTable();
        $conn = Config::get('auto-archive.archive_connection');

        $sm = DB::connection($model->getConnectionName())->getDoctrineSchemaManager();
        $columns = $sm->listTableColumns($table);

        $class = 'Create' . Str::studly($archive) . 'Table';
        $time = date('Y_m_d_His');
        $file = "{$time}_create_{$archive}_table.php";
        $path = base_path($this->option('path') ?: 'database/migrations/archive');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $stub = $this->buildStub($class, $conn, $archive, $columns);
        file_put_contents("{$path}/{$file}", $stub);
        $this->info("Created migration: {$path}/{$file}");
    }

    protected function buildStub($class, $conn, $archive, $columns)
    {
        $up = [];
        foreach ($columns as $col) {
            $name = $col->getName();
            $type = $col->getType()->getName();
            $nullable = $col->getNotnull() ? '' : '->nullable()';
            switch ($type) {
                case 'integer': $m = "\$table->integer('$name')"; break;
                case 'bigint': $m = "\$table->bigInteger('$name')"; break;
                case 'string': $len = $col->getLength(); $m = "\$table->string('$name',$len)"; break;
                case 'text': $m = "\$table->text('$name')"; break;
                case 'datetime': $m = "\$table->dateTime('$name')"; break;
                case 'date': $m = "\$table->date('$name')"; break;
                default: $m = "// TODO: {$type} {$name}";
            }
            $up[] = "            {$m}{$nullable};";
        }
        $up[] = "            \$table->timestamp('archived_at')->nullable();";

        $upBlock = implode("\n", $up);

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {$class} extends Migration
{
    public function connection() { return '{$conn}'; }

    public function up()
    {
        Schema::connection(\$this->connection())->create('{$archive}', function (Blueprint \$table) {
{$upBlock}
        });
    }

    public function down()
    {
        Schema::connection(\$this->connection())->dropIfExists('{$archive}');
    }
}
PHP;
    }
}