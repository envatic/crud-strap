<?php

namespace Envatic\CrudStrap\Commands;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class CrudMigrationCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:migration
                            {name : The name of the migration.}
                            {--schema= : The name of the schema.}
                            {--indexes= : The fields to add an index to.}
                            {--foreign-keys= : Foreign keys.}
                            {--pk=id: The name of the primary key.}
                            {--uuid-pk: Use UUId primary key?.}
                            {--f|force : Force delete.}
                            {--prefix= : Used to check if crude is complete.}
                            {--soft-deletes : Include soft deletes fields.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     *  Migration column types collection.
     *
     * @var array
     */

    protected $typeLookup = [
        'datetime' => 'dateTime',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',
        'bigint' => 'bigInteger',
        'mediumint' => 'mediumInteger',
        'tinyint' => 'tinyInteger',
        'smallint' => 'smallInteger',
        'bigIncrements' => 'bigIncrements',
        'bigInteger' => 'bigInteger',
        'binary' => 'binary',
        'boolean' => 'boolean',
        'char' => 'char',
        'dateTimeTz' => 'dateTimeTz',
        'dateTime' => 'dateTime',
        'date' => 'date',
        'decimal' => 'decimal',
        'double' => 'double',
        'enum' => 'enum',
        'float' => 'float',
        'foreignId' => 'foreignId',
        'foreignIdFor' => 'foreignIdFor',
        'foreignUuid' => 'foreignUuid',
        'geometryCollection' => 'geometryCollection',
        'geometry' => 'geometry',
        'id' => 'id',
        'increments' => 'increments',
        'integer' => 'integer',
        'ipAddress' => 'ipAddress',
        'json' => 'json',
        'jsonb' => 'jsonb',
        'lineString' => 'lineString',
        'longText' => 'longText',
        'macAddress' => 'macAddress',
        'mediumIncrements' => 'mediumIncrements',
        'mediumInteger' => 'mediumInteger',
        'mediumText' => 'mediumText',
        'morphs' => 'morphs',
        'multiPoint' => 'multiPoint',
        'multiPolygon' => 'multiPolygon',
        'nullableMorphs' => 'nullableMorphs',
        'nullableTimestamps' => 'nullableTimestamps',
        'nullableUuidMorphs' => 'nullableUuidMorphs',
        'point' => 'point',
        'polygon' => 'polygon',
        'rememberToken' => 'rememberToken',
        'set' => 'set',
        'smallIncrements' => 'smallIncrements',
        'smallInteger' => 'smallInteger',
        'softDeletesTz' => 'softDeletesTz',
        'softDeletes' => 'softDeletes',
        'string' => 'string',
        'text' => 'text',
        'timeTz' => 'timeTz',
        'time' => 'time',
        'timestampTz' => 'timestampTz',
        'timestamp' => 'timestamp',
        'timestampsTz' => 'timestampsTz',
        'timestamps' => 'timestamps',
        'tinyIncrements' => 'tinyIncrements',
        'tinyInteger' => 'tinyInteger',
        'tinyText' => 'tinyText',
        'unsignedBigInteger' => 'unsignedBigInteger',
        'unsignedDecimal' => 'unsignedDecimal',
        'unsignedInteger' => 'unsignedInteger',
        'unsignedMediumInteger' => 'unsignedMediumInteger',
        'unsignedSmallInteger' => 'unsignedSmallInteger',
        'unsignedTinyInteger' => 'unsignedTinyInteger',
        'uuidMorphs' => 'uuidMorphs',
        'uuid' => 'uuid',
        'year' => 'year',
    ];

    protected $migrated = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'current_team_id',
        'profile_photo_path',
        'timestamps',
        'rememberToken'
    ];

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $isExists = $this->migrationExists($this->getNameInput());
        if ((!$this->hasOption('force') ||
            !$this->option('force'))
            && $isExists
        ) {
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
        $tableName = $this->argument('name');
        $exists = $this->migrationFile(strtolower($tableName));
        $isUser = strtolower($tableName) == 'users';
        $type = $isUser && $exists ? 'Update' : 'Create';
        $className = $type . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Table';
        $fieldsToIndex = trim($this->option('indexes')) != '' ? explode(',', $this->option('indexes')) : [];
        $foreignKeys = trim($this->option('foreign-keys')) != '' ? explode(',', $this->option('foreign-keys')) : [];
        $schema = rtrim($this->option('schema'), ';');
        $fields = explode(';', $schema);
        $data = array();
        if ($schema) {
            $x = 0;
            foreach ($fields as $field) {
                $fieldArray = explode('#', $field);
                $data[$x]['name'] = trim($fieldArray[0]);
                $modifications = explode('|', $fieldArray[1]);
                $type = array_shift($modifications);
                $data[$x]['type'] = $type;
                if ((Str::startsWith($type, 'select') || Str::startsWith($type, 'enum')) && isset($fieldArray[2])) {
                    $options = trim($fieldArray[2]);
                    $data[$x]['options'] = str_replace('options=', '', $options);
                }
                //string:30|default:'ofumbi'|nullable
                $data[$x]['modifiers'] = [];
                if (count($modifications)) {
                    $modifierLookup = [
                        'comment',
                        'default',
                        'first',
                        'nullable',
                        'unsigned',
                        'unique',
                        'charset',
                        'useCurrent',
                        'constrained',
                        'onDelete',
                        'onUpdate',
                    ];
                    foreach ($modifications as $modification) {
                        $variables = explode(':', $modification);
                        $modifier =   array_shift($variables);
                        if (!in_array(trim($modifier), $modifierLookup)) continue;
                        $vars = $variables[0] ?? "";
                        $data[$x]['modifiers'][] = "->" . trim($modifier) . "(" . $vars . ")";
                    }
                }
                $x++;
            }
        }

        $tabIndent = '    ';
        $schemaFields = '';
        $schemaDropFields = '';
        foreach ($data as $item) {
            $data_type = explode(':', $item['type']);
            $item_type = array_shift($data_type);
            if (
                $isUser
                && in_array(trim($item['name']), $this->migrated)
                || in_array(trim($item_type), $this->migrated)
            ) {
                continue;
            }
            $variables = isset($data_type[0]) ? "," . $data_type[0] : "";
            $schemaDrop = "\$table->dropColumn('{$item['name']}')";
            $schemaDropForiegn = "\$table->dropForeign(['{$item['name']}']);";
            $schemaDropFields .= in_array($item_type, ['foreignId', 'foreignIdFor', 'foreignUuid',])
            ? $schemaDropForiegn
                : $schemaDrop;
            if (isset($this->typeLookup[$item_type])) {
                $type = $this->typeLookup[$item_type];
                if (!empty($item['options'])) {
                    $enumOptions = array_keys(json_decode($item['options'], true));
                    $enumOptionsStr = implode(",", array_map(function ($string) {
                        return '"' . $string . '"';
                    }, $enumOptions));
                    $schemaFields .= "\$table->enum('" . $item['name'] . "', [" . $enumOptionsStr . "])";
                } elseif ($item['name'] == "uuid") {
                    $schemaFields .= "\$table->uuid('" . $item['name'] . "')";
                } else {
                    $schemaFields .= "\$table->" . $type . "('" . $item['name'] . "'" . $variables . ")";
                }
            } else {
                if (isset($item['options']) && $item['options']) {
                    $enumOptions = array_keys(json_decode($item['options'], true));
                    $enumOptionsStr = implode(",", array_map(function ($string) {
                        return '"' . $string . '"';
                    }, $enumOptions));
                    $schemaFields .= "\$table->enum('" . $item['name'] . "', [" . $enumOptionsStr . "])";
                } elseif ($item['name'] == "uuid") {
                    $schemaFields .= "\$table->uuid('" . $item['name'] . "')";
                } else {
                    $schemaFields .= "\$table->string('" . $item['name'] . "'" . $variables . ")";
                }
            }

            // Append column modifier
            $schemaFields .= implode("", $item['modifiers']);
            $schemaFields .= ";\n" . $tabIndent . $tabIndent . $tabIndent;
            $schemaDropFields .= ";\n" . $tabIndent . $tabIndent . $tabIndent;
        }

        // add indexes and unique indexes as necessary
        foreach ($fieldsToIndex as $fldData) {
            $line = trim($fldData);

            // is a unique index specified after the #?
            // if no hash present, we append one to make life easier
            if (strpos($line, '#') === false) {
                $line .= '#';
            }

            // parts[0] = field name (or names if pipe separated)
            // parts[1] = unique specified
            $parts = explode('#', $line);
            if (strpos($parts[0], '|') !== 0) {
                $fieldNames = "['" . implode("', '", explode('|', $parts[0])) . "']"; // wrap single quotes around each element
            } else {
                $fieldNames = trim($parts[0]);
            }

            if (count($parts) > 1 && $parts[1] == 'unique') {
                $schemaFields .= "\$table->unique(" . trim($fieldNames) . ")";
            } else {
                $schemaFields .= "\$table->index(" . trim($fieldNames) . ")";
            }
            $schemaFields .= ";\n" . $tabIndent . $tabIndent . $tabIndent;
        }

        // foreignKeysField
        $foreignKeysFields = "";
        foreach ($foreignKeys as $fk) {
            $line = trim($fk);
            $parts = explode('#', $line);
            // if we don't have three parts, then the foreign key isn't defined properly
            // --foreign-keys="foreign_entity_id#id#foreign_entity#onDelete#onUpdate"
            $iname = trim($parts[0]);
            if (count($parts) == 3) {
                $schemaDropFields = "\$table->dropForeign(['{$iname}']);";
                $foreignKeysFields .= "\$table->foreign('" . trim($parts[0]) . "')"
                    . "->references('" . trim($parts[1]) . "')->on('" . trim($parts[2]) . "')";
            } elseif (count($parts) == 4) {
                $schemaDropFields = "\$table->dropForeign(['{$iname}']);";
                $foreignKeysFields .= "\$table->foreign('" . trim($parts[0]) . "')"
                    . "->references('" . trim($parts[1]) . "')->on('" . trim($parts[2]) . "')"
                    . "->onDelete('" . trim($parts[3]) . "')" . "->onUpdate('" . trim($parts[3]) . "')";
            } elseif (count($parts) == 5) {
                $schemaDropFields = "\$table->dropForeign(['{$iname}']);";
                $foreignKeysFields .= "\$table->foreign('" . trim($parts[0]) . "')"
                    . "->references('" . trim($parts[1]) . "')->on('" . trim($parts[2]) . "')"
                    . "->onDelete('" . trim($parts[3]) . "')" . "->onUpdate('" . trim($parts[4]) . "')";
            } else {
                continue;
            }
        }
        $endcolon = $foreignKeysFields == "" ? "" : ";";
        $foreignKeysFields .= $endcolon . "\n" . $tabIndent . $tabIndent;
        $primaryKey = $this->option('pk');
        $softDeletes = $this->option('soft-deletes');
        $softDeletesSnippets = '';
        if ($softDeletes) {
            $softDeletesSnippets = "\$table->softDeletes();\n" . $tabIndent . $tabIndent . $tabIndent;
            $softDeletesDropSnippets = "\$table->dropSoftDeletes();\n" . $tabIndent . $tabIndent . $tabIndent;
        }
        $schema_type = $isUser ? 'table' : 'create';
        $schemaDrop =
            "Schema::table('" . $tableName . "', function (Blueprint \$table) { \n" . $tabIndent . $tabIndent . $tabIndent .
            $schemaDropFields .
            $softDeletesDropSnippets . "\n" . $tabIndent . $tabIndent . "});";
        $creatSchemaUp = "Schema::create('" . $tableName . "', function (Blueprint \$table) { \n" . $tabIndent . $tabIndent . $tabIndent .
            "\$table->bigIncrements('" . $primaryKey . "');\n" . $tabIndent . $tabIndent . $tabIndent .
            $schemaFields . "\$table->timestamps();\n" . $tabIndent . $tabIndent . $tabIndent .
            $softDeletesSnippets .
            substr($foreignKeysFields, 0, -1) . "});";
        $updateSchemaUp = "Schema::table('" . $tableName . "', function (Blueprint \$table) { \n" . $tabIndent . $tabIndent . $tabIndent .
            $schemaFields .
            $softDeletesSnippets .
            substr($foreignKeysFields, 0, -1) . "});";
        $schemaDown = $isUser
            ? $schemaDrop
            : "Schema::drop('" . $tableName . "');";
        $schemaUp = $isUser ? $updateSchemaUp : $creatSchemaUp;
        return $this->replaceSchemaUp($stub, $schemaUp)
            ->replaceSchemaDown($stub, $schemaDown)
            ->replaceClass($stub, $className);
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function migrationExists($rawName)
    {
        $is_user =  strtolower($rawName)  == 'users';
        $exist = $this->migrationFile($rawName);
        $type = $is_user && $exist ? 'update_' : 'create_';
        $migration = $type  . strtolower($rawName) . '_table';
        $filesNames = File::files(base_path() . '/database/migrations/');
        $exists = false;
        foreach ($filesNames as $file) {
            if (Str::contains($file->getFilename(),  $migration)) {
                $exists = $file->getFilename();
                if ($this->option('force')) {
                    File::delete($file->getPathname());
                    $this->warn('Migration deleted:' . $file->getFilename());
                    $exists = false;
                }
            }
        }
        if ($exists) {
            $this->warn('Migration File Found: ' . $exists);
            return true;
        }
        return false;
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function migrationFile($rawName)
    {
        $migration = 'create_' . strtolower($rawName) . '_table';
        $filesNames = File::files(base_path() . '/database/migrations/');
        foreach ($filesNames as $file) {
            if (Str::contains($file->getFilename(),  $migration)) {
                return true;
            }
        }
        return false;
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
        $exists = $this->migrationFile($name);
        $type = $name == 'users' && $exists ? '_update_' : '_create_';
        $ms = microtime(true);
        $mst = Str::of($ms)->explode('.')->all();
        $sss = $mst[1] ?? 0000;
        $datePrefix = date('Y_m_d_His');
        $nameTime = $this->option('prefix') ?? $datePrefix . $sss;
        return database_path('/migrations/') . $nameTime . $type . $name . '_table.php';
    }
    /**
     * Replace the schema_up for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaUp
     *
     * @return $this
     */
    protected function replaceSchemaUp(&$stub, $schemaUp)
    {
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
    protected function replaceSchemaDown(&$stub, $schemaDown)
    {
        $stub = str_replace('{{schema_down}}', $schemaDown, $stub);

        return $this;
    }
}
