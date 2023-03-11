<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\Faker;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =
    'crud:generate
                            {name : The name of the Crud.}
                            {--fields= : Field names for the form & migration.}
                            {--fields_from_file= : Fields from a json file.}
                            {--validations= : Validation rules for the fields.}
                            {--controller-namespace= : Namespace of the controller.}
                            {--model-namespace= : Namespace of the model inside "app" dir.}
                            {--pk=id : The name of the primary key.}
                            {--pagination=25 : The amount of models per page for index pages.}
                            {--indexes= : The fields to add an index to.}
                            {--foreign-keys= : The foreign keys for the table.}
                            {--relationships= : The relationships for the model.}
                            {--route-group= : Prefix of the route group.}
                            {--view-path= : The name of the view path.}
                            {--faker= : Factory Fields.}
                            {--prefix= : Migration Datetime Prefix.}
                            {--f|force : Force delete.}
                            {--casts= : The Model Field to cast.}
                            {--form-helper=html : Helper for generating the form.}
                            {--locales=en : Locales language type.}
                            {--soft-deletes : Include soft deletes fields.}
							{--only=all  : Create only certain crude items.}
							{--stub-path= : Optional name of the stubs folder in the view stubs dir.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Crud including controller, model, views & migrations.';

    /** @var string  */
    protected $routeName = '';

    /** @var string  */
    protected $controller = '';
    /** @var string  */
    protected $routePrefix = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $tabIndent = '    ';
        $name = $this->argument('name');
        $nameStr = Str::of($name);
        $modelName = $nameStr->singular();
        $modelUse = "";
        $modelUseTrait = "";
        $migrationName = $nameStr->plural()->snake();
        $tableName = $migrationName;
        $routeGroup = $this->option('route-group');
        $this->routeName =   $nameStr->lower()->snake('-');
        $this->routePrefix = ($routeGroup) ? $routeGroup . '.' . $this->routeName : $this->routeName;
        $perPage = intval($this->option('pagination'));
        $controllerNamespace = ($this->option('controller-namespace')) ? $this->option('controller-namespace') . '\\' : '';
        $api = Str::startsWith(strtolower($controllerNamespace), 'api\\');
        $modelNamespace = ($this->option('model-namespace')) ? trim($this->option('model-namespace')) . '\\' : '';
        $fields = rtrim($this->option('fields'), ';');
        $viewFields = $fields;
        $only = array_map('trim', explode(',', $this->option('only')));
        $force = $this->option('force') ? ['--force' => true] : [];
        $all = in_array('all', $only);
        if ($this->option('fields_from_file')) {
            $fields = $this->processJSONFields($this->option('fields_from_file'));
            list($viewFields, $fillableArray) = $this->processJSONViewFields($this->option('fields_from_file'));
        }

        $primaryKey = $this->option('pk');
        $viewPath = $this->option('view-path');

        $foreignKeys = $this->option('foreign-keys');

        if ($this->option('fields_from_file')) {
            $foreignKeys = $this->processJSONForeignKeys($this->option('fields_from_file'));
        }
        $fakerFields = $this->option('faker');
        if ($this->option('fields_from_file')) {
            list($fakerFields, $fakerUse) = $this->processFakerFields($this->option('fields_from_file'));
        }


        $validations = trim($this->option('validations'));
        if ($this->option('fields_from_file')) {
            $validations = $this->processJSONValidations($this->option('fields_from_file'));
        }
        $modelCasts = trim($this->option('casts'));
        if ($this->option('fields_from_file')) {
            $modelCasts = $this->processModelCasts($this->option('fields_from_file'));
        }
        $enumData = [];
        $fieldsArray = explode(';', $fields);
        $migrationFields = '';
        $transformArray = [];
        $hasUuid = false;
        $resourceArray = [$tabIndent . $tabIndent . $tabIndent . "'id'=>\$this->id,"];
        foreach ($fieldsArray as $item) {
            $spareParts = explode('#', trim($item));
            $transformArray[] = $tabIndent . $tabIndent . $tabIndent . "'" . $spareParts[0] . "'=> \$" . strtolower($modelName) . '->' . $spareParts[0] . ',';
            $resourceArray[] = $tabIndent . $tabIndent . $tabIndent . "'" . $spareParts[0] . "'=> \$this->" . $spareParts[0] . ',';
            $migrations = $spareParts[0] . '#' . $spareParts[1];
            if ($spareParts[0] == 'uuid') {
                $hasUuid = true;
            }
            if (isset($spareParts[2]) && Str::contains($spareParts[2], 'options')) {
                if (!Str::contains($spareParts[1], ['select', 'enum'])) {
                    $migrations .= '#' . $spareParts[2];
                } elseif (($all || in_array('enums', $only))) {
                    $fname = Str::of($spareParts[0])->lower()->ucfirst();
                    $enumClass = ucfirst($modelName) . $fname;
                    $enumData[$enumClass] = $spareParts[2];
                    $modelUse .= "use \\App\\Enums\\" . $enumClass . ';';
                    $modelCasts .= $spareParts[0] . '#' . $enumClass . '::class;';
                    $migrations  = $spareParts[0] . '#string';
                } else {
                    $migrations .= '#' . $spareParts[2];
                }
            }
            $migrationFields .= $migrations . ';';
        }
        if ($hasUuid) {
            $modelUse .= "use Envatic\\CrudStrap\\Traits\\HasUuid;";
            $modelUseTrait .= 'use HasUuid;';
        }
        $tab = "    ";
        $commaSeparetedString = collect($fillableArray)->implode("',\n{$tab}{$tab}'");
        $fillable = "[\n        '" . $commaSeparetedString . "'\n   ]";
        $commaSeparetedArray = implode("\n", $transformArray);
        $transform  = "[\n" . $commaSeparetedArray . "\n" . $tabIndent . $tabIndent . "]";
        $locales = $this->option('locales');
        $indexes = $this->option('indexes');
        $stubs = $this->option('stub-path');
        $relationships = $this->option('relationships');
        if ($this->option('fields_from_file')) {
            $relationships = $this->processJSONRelationships($this->option('fields_from_file'));
        }
        $relations = explode(';', $relationships);
        $hasMophs = false;
        if (count($relations)) {
            foreach ($relations as $item) {
                $parts = explode('#', $item);
                $relationshipName = $parts[0];
                $relationshipType = $parts[1] ?? null;
                if (is_null($relationshipType)) continue;
                $collection  = Str::contains($relationshipType, 'Many');
                if ($relationshipType == 'morphTo') {
                    $hasMophs = true;
                }
                if (count($parts) != 3) {
                    continue;
                }
                $args = explode('|', trim($parts[2]));
                $rl = explode('\\', $args[0]);
                $relationshipModel = end($rl);
                $resourceArray[] = $collection
                    ? $tabIndent . $tabIndent . $tabIndent . "'" . $relationshipName . "'=> " . $relationshipModel . "::collection(\$this->whenLoaded('" . $relationshipName . "')),"
                    : $tabIndent . $tabIndent . $tabIndent . "'" . $relationshipName . "'=> new " . $relationshipModel . "(\$this->whenLoaded('" . $relationshipName . "')),";
            }
        }
        $commaResource = implode("\n", $resourceArray);
        $resource  = "[\n" . $commaResource . "\n" . $tabIndent . $tabIndent . "]";
        $formHelper = $this->option('form-helper');
        $softDeletes = $this->option('soft-deletes');
        $skipMigration = explode(',', str_replace(' ', '', config('crudstrap.skip.migration') ?? ""));
        $skipTable = in_array($tableName, $skipMigration);
        if (($all || in_array('migration', $only)) && !$skipTable) {
            $this->call('crud:migration', array_filter([
                'name' => $migrationName,
                '--schema' => $migrationFields,
                '--prefix' => $this->option('prefix'),
                '--pk' => $primaryKey,
                '--indexes' => $indexes,
                '--foreign-keys' => $foreignKeys,
                '--soft-deletes' => $softDeletes,
                '--force' => $force,
            ]));
        }
        $skipPolicy = explode(',', str_replace(' ', '', config('crudstrap.skip.policy') ?? ""));
        $skipPol = in_array($tableName, $skipPolicy);
        if (($all || in_array('policy', $only)) && !$skipPol)
            $this->call('make:policy', array_filter([
                'name' => $modelName . 'Policy',
                '--model' => $modelName,
            ]));
        $skipTranformer = explode(',', str_replace(' ', '', config('crudstrap.skip.transformer') ?? ""));
        $skipTrn = in_array($tableName, $skipTranformer);
        if (($all || in_array('transformer', $only)) && !$skipTrn)
            $this->call('crud:transformer', array_filter([
                'name' => $modelName . 'Transformer',
                '--model' => $modelName,
                '--transform-array' => $transform,
                '--model-namespace' => $modelNamespace,
                '--relationships' => $relationships,
                '--force' => $force,
            ]));
        $skipResource = explode(',', str_replace(' ', '', config('crudstrap.skip.resource') ?? ""));
        $skipRes = in_array($tableName, $skipResource);
        if (($all || in_array('resource', $only)) && !$skipRes)
            $this->call('crud:resource', array_filter([
                'name' => $modelName,
                '--resource-array' => $resource,
                '--has-morphs' => $hasMophs,
                '--force' => $force,
            ]));
        $skipFactory = explode(',', str_replace(' ', '', config('crudstrap.skip.factory') ?? ""));
        $skipFac = in_array($tableName, $skipFactory);
        if ($all || in_array('factory', $only) && !$skipFac && $fakerFields != "")
            $this->call('crud:dbfactory', array_filter([
                'name' => $modelName . 'Factory',
                '--model' => $modelName,
                '--faker' => $fakerFields,
                '--use' => $fakerUse,
                '--force' => $force,
            ]));
        $skipEnums = explode(',', str_replace(' ', '', config('crudstrap.skip.enums') ?? ""));
        $skipEnu = in_array($tableName, $skipEnums);
        if (($all || in_array('enums', $only)) && !$skipEnu) {
            foreach ($enumData as $class => $data) {
                $this->call('crud:enum', array_filter([
                    'name' => $class,
                    '--model' => $modelName,
                    '--enum' =>  $data,
                    '--force' => $force,
                ]));
            }
        }

        $skipController = explode(',', str_replace(' ', '', config('crudstrap.skip.controller') ?? ""));
        $skipCon = in_array($tableName, $skipController);
        if (($all || in_array('controller', $only)) && !$skipCon) {
            $this->call('crud:controller', array_filter([
                'name' => $controllerNamespace . $name . 'Controller',
                '--crud-name' => $name,
                '--model-name' => $modelName,
                '--model-namespace' => $modelNamespace,
                '--view-path' => $viewPath,
                '--route-group' => $routeGroup,
                '--pagination' => $perPage,
                '--relationships' => $relationships,
                '--fields' => $fields,
                '--validations' => $validations,
                '--inertia' =>  $formHelper == 'inertia',
                '--force' => $force,
                '--api' => $api
            ]));
        }
        $skipModel = explode(',', str_replace(' ', '', config('crudstrap.skip.model') ?? ""));
        $skipMod = in_array($tableName, $skipModel);
        if (($all || in_array('model', $only)) && !$skipMod) {
            if ($all || in_array('factory', $only)) {
                $modelUse .= 'use Illuminate\Database\Eloquent\Factories\HasFactory;';
                $modelUseTrait .= '    use HasFactory;';
            }

            $this->call('crud:model', array_filter([
                'name' => $modelNamespace . $modelName,
                '--fillable' => $fillable,
                '--table' => $tableName,
                '--pk' => $primaryKey,
                '--casts' => $modelCasts,
                '--relationships' => $relationships,
                '--soft-deletes' => $softDeletes,
                '--use' => $modelUse,
                '--use-trait' => $modelUseTrait,
                '--force' => $force,
            ]));
            //$this->call('crud:observer', ['name' =>$modelName.'Observer', '--model' => $modelNamespace . $modelName]);
        }
        $skipView = explode(',', str_replace(' ', '', config('crudstrap.skip.view') ?? ""));
        $skipVie = in_array($tableName, $skipView);
        if (($all || in_array('view', $only)) && !$skipVie)
            $this->call('crud:view', array_filter([
                'name' => $name,
                '--fields' => $viewFields,
                '--validations' => $validations,
                '--view-path' => $viewPath,
                '--route-group' => $routeGroup,
                '--localize' => $all || in_array('lang', $only),
                '--pk' => $primaryKey,
                '--form-helper' => $formHelper,
                '--stub-path' => $stubs,
                '--inertia' =>  $formHelper == 'inertia',
                '--force' => $force,
            ]));
        $skipLang = explode(',', str_replace(' ', '', config('crudstrap.skip.lang') ?? ""));
        $skipLan = in_array($tableName, $skipLang);
        if (($all || in_array('lang', $only)) && !$skipLan) {
            $this->call('crud:lang', [
                'name' => $name,
                '--fields' => $fields . ";" . $modelName . "#" . $modelName,
                '--locales' => $locales
            ]);
        }

        // For optimizing the class loader
        $this->callSilent('optimize');
        if ($all || in_array('route', $only)) {
            // Updating the Http/routes.php file
            $routeFile = base_path('routes/web.php');
            if ($api) {
                $routeFile = base_path('routes/api.php');
            }
            if (file_exists($routeFile)) {
                $this->controller =  $name . 'Controller';
                $routeContent = $api ? $this->addApiRoutes() : $this->addRoutes();
                $routes = "\n" . implode("\n", $routeContent);
                $file = File::get($routeFile);
                if (preg_match('/(\#' . $this->routeName . ')/', $file, $matches) == 1) {
                    $outfile = preg_replace('/(\#' . $this->routeName . ')(.*?)(\#' . $this->routeName . ')/s', $routes, $file);
                    return File::replace($routeFile,   $outfile);
                }
                $isAdded = File::append($routeFile, $routes);
                if ($isAdded) {
                    $this->info('Crud/Resource route added to ' . $routeFile);
                } else {
                    $this->info('Unable to add the route to ' . $routeFile);
                }
            }
        }
    }

    protected function processJSONViewFields($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        $fieldsString = '';
        $validations = $fields->validations ?? null;
        $rules = $fields->validations ?? null;
        $relations = $fields->foreign_keys ?? null;
        $fillableArray = [];
        foreach ($fields->fields as $field) {
            $fillableArray[] = Str::of($field->name)->replace(':', '|')->explode('|')->first();
            $rules = strlen($field->rules ?? '') > 0;
            if (!$validations && !$rules) continue;
            if ($validations && !$rules) {
                $validated = false;
                foreach ($validations as $validation) {
                    $valid = $validation->field ?? null;
                    if ($valid && $valid == $field->name)
                        $validated = true;
                }
                if (!$validated) continue;
            }
            if (Str::startsWith($field->type, 'select') || Str::startsWith($field->type, 'enum')) {
                $fieldsString .= $field->name . '#' . $field->type . '#options=' . json_encode($field->options) . ';';
                continue;
            }
            $name = "";
            if (Str::contains($field->type, 'foreignId')) {
                $fname = '#options=' . str_replace("_id", "", $field->name);
                $fieldsString .= $field->name . '#' . $field->type . $fname . ';';
                continue;
            }
            if ($relations && substr($field->name, -3) == "_id") {
                var_dump($field->name . " ----> options\n");
                foreach ($relations as $relation) {
                    if ($relation->column != $field->name) continue;
                    $name = '#options=' . str_replace("_id", "", $field->name);
                    break;
                }
            }
            $fieldsString .= $field->name . '#' . $field->type . $name . ';';
        }
        $fieldsString = rtrim($fieldsString, ';');
        return [$fieldsString, $fillableArray];
    }

    protected function processJSONFields($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        $fieldsString = '';
        $relations = $fields->foreign_keys ?? null;
        foreach ($fields->fields as $field) {
            $type = $field->type ?? null;
            if (!$type) {
                $this->error('Invalid Field Mark up');
                $this->info("In $file");
                dd($field);
            }
            if (Str::startsWith($field->type, 'select') || Str::startsWith($field->type, 'enum')) {
                $fieldsString .= $field->name . '#' . $field->type . '#options=' . json_encode($field->options) . ';';
                continue;
            }
            $name = "";
            if (Str::startsWith($field->type, 'foreignId')) {
                $name = '#options=' . str_replace("_id", "", $field->name);
                $fieldsString .= $field->name . '#' . $field->type . $name . ';';
                continue;
            }
            if ($relations && substr($field->name, -3) == "_id") {
                foreach ($relations as $relation) {
                    if ($relation->column != $field->name) continue;
                    $name = '#options=' . str_replace("_id", "", $field->name);
                    break;
                }
            }
            $fieldsString .= $field->name . '#' . $field->type . $name . ';';
        }
        $fieldsString = rtrim($fieldsString, ';');
        return $fieldsString;
    }

    protected function addRoutes()
    {
        $tb = '    ';
        $routeName = Str::of($this->routeName)->lower()->snake();
        $routeVar = Str::of($this->routeName)->lower()->snake()->singular();
        return [
            "#$routeName
 Route::name('{$this->routePrefix}.')->controller({$this->controller}::class)->group(function () {
    Route::get('/{$routeName}', 'index')->name('index');
    Route::get('/{$routeName}/create', 'create')->name('create');
    Route::post('/{$routeName}/store', 'store')->name('store');
    Route::get('/{$routeName}/{{$routeVar}}/show', 'show')->name('show');
    Route::get('/{$routeName}/{{$routeVar}}/edit', 'edit')->name('edit');
    Route::put('/{$routeName}/{{$routeVar}}', 'update')->name('update');
    Route::delete('/{$routeName}/{{$routeVar}}', 'destroy')->name('destroy');
});
#$routeName"
        ];
    }

    protected function addApiRoutes()
    {
        $tb = '    ';
        return [
            $tb . $tb . "Route::apiResource('" . $this->routeName . "', '" . $this->controller . "',[
" . $tb . $tb . $tb . "'names' => [
" . $tb . $tb . $tb . $tb . "'index'   => '" . $this->routePrefix . ".index',
" . $tb . $tb . $tb . $tb . "'store'   => '" . $this->routePrefix . ".store',
" . $tb . $tb . $tb . $tb . "'show'   => '" . $this->routePrefix . ".show',
" . $tb . $tb . $tb . $tb . "'update'   => '" . $this->routePrefix . ".update',
" . $tb . $tb . $tb . $tb . "'destroy' => '" . $this->routePrefix . ".destroy',
" . $tb . $tb . $tb . "],
" . $tb . $tb . "]);
" . $tb . $tb . "Route::post('" . $this->routeName . "/toggle-status/{id}',['as'=>'" . $this->routePrefix . ".toggle_status','uses'=>'" . $this->controller . "@toggle_status']);
" . $tb . $tb . "Route::post('" . $this->routeName . "/mass-toggle',['as'=>'" . $this->routePrefix . ".masstoggle','uses'=>'" . $this->controller . "@toggle_statuses']);
" . $tb . $tb . "Route::post('" . $this->routeName . "/mass-delete',['as'=>'" . $this->routePrefix . ".massdelete','uses'=>'" . $this->controller . "@delete']);
"
        ];
    }

    /**
     * Process the JSON Foreign keys.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function processJSONForeignKeys($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        if (!property_exists($fields, 'foreign_keys')) {
            return '';
        }
        $foreignKeysString = '';
        foreach ($fields->foreign_keys as $foreign_key) {
            $foreignKeysString .= $foreign_key->column . '#' . $foreign_key->references . '#' . $foreign_key->on;
            if (property_exists($foreign_key, 'onDelete')) {
                $foreignKeysString .= '#' . $foreign_key->onDelete;
            }
            if (property_exists($foreign_key, 'onUpdate')) {
                $foreignKeysString .= '#' . $foreign_key->onUpdate;
            }
            $foreignKeysString .= ',';
        }

        $foreignKeysString = rtrim($foreignKeysString, ',');
        return $foreignKeysString;
    }


    /**
     * Process the JSON Relationships.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function processJSONRelationships($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        if (!isset($fields->relationships)) {
            return '';
        }

        $relationsString = '';
        foreach ($fields->relationships as $relation) {
            $class = $relation->class ?? "";
            $relationsString .= $relation->name . '#' . $relation->type . '#' . $class . ';';
        }
        $relationsString = rtrim($relationsString, ';');
        return $relationsString;
    }

    protected function processJSONValidations($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        $validationsString = '';
        if (property_exists($fields, 'validations')) {
            foreach ($fields->validations as $validation) {
                $validationsString .= $validation->field . '#' . $validation->rules . ';';
            }
        }
        if (property_exists($fields, 'fields')) {
            foreach ($fields->fields as $field) {
                if (property_exists($field, 'rules')) {
                    $validationsString .= $field->name . '#' . $field->rules . ';';
                }
            }
        }
        $validationsString = rtrim($validationsString, ';');
        return $validationsString;
    }

    protected function processFakerFields($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        $fakerFields = '';
        $use = "";
        foreach ($fields->fields as $field) {
            $modifiers = '';
            $fieldTypesArray = explode('|', $field->type);
            $fieldTypes = explode(':', $fieldTypesArray[0]);
            $fieldType = $fieldTypes[0];
            $vars = "";
            if (Str::contains($field->type, 'foreignId')) {
                if (!isset($fields->relationships)) continue;
                foreach ($fields->relationships as $rel) {
                    $class = $rel->class ?? 'NOCLASS';
                    if (!isset($rel->class) || !Str::contains($rel->class, $field->name))
                        continue;
                    $class = explode('|', $rel->class);
                    $model = $class[0];
                    $use .= Str::contains("\\App", $model) ? "use " . $model . ';' : "use App\\Models\\" . $model . ";";
                    $fakerFields .= $field->name . "#{$model}::factory();";
                    continue 2;
                }
            };

            if (isset($field->faker)) {
                $fakerFields .= $field->name . '#' . $field->faker . ';';
                continue;
            }
            if (Str::contains($field->type, 'unique')) {
                $modifiers .= "|unique";
            }
            if (in_array($fieldType, ['char', 'string']) && isset($fieldTypes[1]) && is_numeric($fieldTypes[1])) {
                $vars = ':' . $fieldTypes[1];
            }
            if (
                Str::contains($field->type, 'double')
                || Str::contains($field->type, 'float')
                || Str::contains($field->type, 'decimal')
                || Str::contains($field->type, 'unsignedDecimal')
            ) {
                $types = explode(':', str_replace([',', '|'], ':', $field->type));
                if (isset($types[2]) && is_numeric($types[2])) {
                    $vars = ":" . $types[2];
                }
            }
            if (
                (Str::contains($field->type, 'select')
                    || Str::contains($field->type, 'enum'))
                && isset($field->options)
            ) {
                $options = json_decode(json_encode($field->options), true);
                $data = implode("','", array_keys($options));
                $vars = ":['" . $data . "']";
            }
            if (Str::contains($field->name, 'password')) $fieldType = 'password';
            $fakerFunc = Faker::lookUp($fieldType);
            if (!$fakerFunc) continue;
            $fakerFields .= $field->name . '#' . $fakerFunc . $vars . $modifiers
                . ';';;
        }

        $fakerFieldsString = rtrim($fakerFields, ';');
        return [$fakerFieldsString, $use];
    }
    protected function processModelCasts($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        $castsFields = '';
        foreach ($fields->fields as $field) {
            $fieldTypes = explode('|', str_replace(':', '|', $field->type));
            $fieldType = $fieldTypes[0];
            if (isset($field->cast)) {
                $cast = Str::contains($field->cast, '::') ? $field->cast : "'$field->cast'";
                $castsFields .= $field->name . '#' . $cast . ';';
                continue;
            }

            if ($fieldType == 'boolean') {
                $castsFields .= $field->name . "#'boolean';";
                continue;
            }
            if (in_array($fieldType, [
                'dateTimeTz',
                'dateTime',
                'datetime',
                'date',
                'timeTz',
                'time',
                'timestampTz',
                'timestamp',
                'timestampsTz',
            ])) {
                $castsFields .= $field->name . "#'datetime';";
                continue;
            }
        }
        return $castsFields;
    }
}
