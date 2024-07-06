<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\CrudConfig;
use Envatic\CrudStrap\CrudFile;
use Envatic\CrudStrap\Fields\Field;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudViewCommand extends Command
{
    /**
     * The config for this crud
     *
     * @var CrudConfig
     */
    protected CrudConfig $config;

    /**
     * The config for this crud
     *
     * @var CrudFile
     */
    protected CrudFile $crud;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:view
                            {name : The name of the Crud.}
                            {theme : Config theme for this crud.}
                            {crud : Crud json file.}
                            {--f|force : Force delete.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the Crud.';
    protected $viewDirectoryPath;
    protected $delimiter;

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return void
     */

    public function handle()
    {
        $crudJson = json_decode(trim($this->argument('crud')));
        $config = new CrudConfig(...(config('crudstrap.themes.' . trim($this->argument('theme')))));
        $this->config = $config->override($crudJson);
        $this->crud = new CrudFile($crudJson, $this->config, $this->argument('name'));
        $this->delimiter = config('crudstrap.delimiter', ['%%', '%%']);
        $this->viewDirectoryPath = config('crudstrap.custom_template')
            ? config('crudstrap.path') . 'views/'
            : __DIR__ . '/../stubs/views/';
        $this->templateStubs();
        $this->info('View created successfully.');
    }



    /**
     * Generate files from stub
     *
     */
    protected function templateStubs()
    {
        $fields = $this->crud->fields;
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $vars = [
            "{$start}crudName{$end}" => $this->crud->crudName(),
            "{$start}crudNameSingular{$end}" => $this->crud->crudNameSingular(),
            "{$start}primaryKey{$end}" => $this->config->primaryKey,
            "{$start}modelName{$end}" => $this->crud->modelName(),
            "{$start}InputImports{$end}" => $fields->unique(fn (Field $f) => $f->type()->name)->flatMap(function (Field $field) {
                if (!$field->hasValidation()) return;
                return $field->getFormInput()->imports() ?? [];
            })->unique()->implode("\n"),
            "{$start}formFieldsHtml{$end}" =>  $fields->map(function (Field $field) {
                if (!$field->hasValidation()) return;
                $formInput = $field->getFormInput()->render();
                return "\t\t\t\t\t\t $formInput";
            })->implode("\n"),
            "{$start}tableHeaderHtml{$end}" => $fields->map(fn (Field $f) => $f->tableHeaderHtml())
                ->implode("\n"),
            "{$start}tableBodyHtml{$end}" => $fields->map(fn (Field $f) => $f->tableBodyHtml())
                ->implode("\n"),
            "{$start}filledVueForm{$end}" => $fields->map(fn (Field $f) => $f->filledVueForm())
                ->implode(",\n"),
            "{$start}emptyVueForm{$end}" => $fields->map(fn (Field $f) => $f->emptyVueForm())
                ->implode(",\n"),
        ];
        $dynamicViewTemplate = config('crudstrap.dynamic_view_template', ['index', 'create', 'edit']);
        foreach ($dynamicViewTemplate as $name) {
            $vuePath = Str::of($this->config->viewPath)->ucfirst();
            $viewPath = $this->config->viewPath == "" ? "js/Pages/" : "js/Pages/$vuePath/";
            if (!File::exists(resource_path($viewPath)))
                File::makeDirectory(resource_path($viewPath));
            $vuefile = $this->viewDirectoryPath . $name . '.vue.stub';
            $vuePageDir = $viewPath . $this->crud->crudName()->ucfirst() . '/';
            $file = resource_path($vuePageDir . ucfirst($name) . '.vue');
            if (File::exists($vuefile) && File::isFile($vuefile)) {
                if (!File::isDirectory(resource_path($vuePageDir)))
                    File::makeDirectory(path: resource_path($vuePageDir), recursive: true, force: true);
                File::put($file, str_replace(array_keys($vars), array_values($vars), File::get($vuefile)));
                continue;
            }
            $this->error("$name not found");
        }
    }
}
