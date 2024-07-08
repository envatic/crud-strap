<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\Crud;
use Envatic\CrudStrap\Fields\Field;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudMigrationCommand extends BaseCrud
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:migration
                            {name : The name of the migration.}
                            {theme : config theme for the migration.}
                            {crud : Crud json data}
                            {--prefix= : optional timestamp prefix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new migration file';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';
    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->init();
        $migration = 'Create_' . strtolower($this->getNameInput()) . '_table';
        $filesNames = File::files(database_path('/migrations/'));
        $exists = false;
        foreach ($filesNames as $file) {
            if (Str::contains($file->getFilename(),  strtolower($migration))) {
                $exists = $file->getFilename();
                if ($this->config->force) {
                    File::delete($file->getPathname());
                    $this->warn('Migration deleted:' . $file->getFilename());
                    $exists = false;
                }
            }
        }
        if ($exists) {
            $this->error($this->type . ' already exists!');
            return false;
        }
        parent::handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . '/migration.stub'
            : __DIR__ . '/../stubs/migration.stub';
    }


    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $tableName = $this->getNameInput();
        $className = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Table';
        return $this->replaceSchemaUp($stub)
            ->replaceSchemaDown($stub)
            ->replaceClass($stub, $className);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);
        $type =  '_create_';
        $nameTime = value(function () {
            if (!empty($this->option('prefix')))
                return $this->option('prefix');
            $ms = microtime(true);
            $mst = Str::of($ms)->explode('.')->all();
            $sss = $mst[1] ?? 0000;
            $datePrefix = date('Y_m_d_His');
            return $datePrefix . $sss;
        });
        return strtolower(database_path('/migrations/') . $nameTime . $type . $name . '_table.php');
    }
    /**
     * Replace the schema_up for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaUp
     *
     * @return $this
     */
    protected function replaceSchemaUp(&$stub)
    {
        $tableName = $this->crud->tableName();
        $schemaFields = $this->dbSchemaString();
        if (count($this->crud->indexes()) > 0) {
            $schemaFields .= $this->dbIndexesString();
        }
        $softDeletesSnippets = $this->config->softdeletes
            ? "\t\t\t\$table->softDeletes();\n\t\t"
            : "\t\t";
        $schemaUp = "Schema::create('" . $tableName . "', function (Blueprint \$table) { \n\t\t\t" .
            "\$table->bigIncrements('" . $this->config->primaryKey . "');\n\t\t\t" .
            $schemaFields . "\t\t\t\$table->timestamps();\n" .
            $softDeletesSnippets . "});";
        $stub = str_replace('{{schema_up}}', $schemaUp, $stub);
        return $this;
    }

    /**
     * Replace the schema_down for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaDown
     *
     * @return $this
     */
    protected function replaceSchemaDown(&$stub)
    {
        $tableName = $this->crud->tableName();
        $schemaDown = "Schema::drop('" . $tableName . "');";
        $stub = str_replace('{{schema_down}}', $schemaDown, $stub);
        return $this;
    }

    protected function dbSchemaString()
    {
        return  $this->crud->fields
            ->map(function (Field $field) {
                if (!$this->config->has('enums') && $field->type()->isEnum()) {
                    $migration = "\$table->enum('" .  $field->name() . "',[" . $field->options()->getDatabaseEnums() . "])";
                    $migration .= $field->type()->getMigrationModifiers();
                    $migration .= ";\n";
                    return $migration;
                }
                return $field->type()->getMigration();
            })
            ->implode("\t\t\t");
    }

    protected function dbIndexesString()
    {
        return collect($this->crud->indexes())->map(function ($index) {
            if (!str($index)->contains(':')) return null;
            [$func, $params] = explode(":", $index, 2) + [null, null];
            $func_inputs = Crud::parseFunctionParams($params);
            if (str($params)->contains(','))
                $func_inputs = "[" . $func_inputs . "]";
            return "\t\t\t\$table->$func($func_inputs)";
        })
            ->filter()
            ->implode(";\n") . ";\n";
    }
}
