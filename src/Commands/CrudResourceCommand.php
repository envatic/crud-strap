<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Foundation\Console\ResourceMakeCommand;

use Envatic\CrudStrap\CrudConfig;
use Envatic\CrudStrap\CrudFile;
use Envatic\CrudStrap\Fields\Field;
use Envatic\CrudStrap\Fields\Relation;

class CrudResourceCommand extends ResourceMakeCommand
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


    public function handle()
    {
        $crudJson = json_decode(trim($this->argument('crud')));
        $config = new CrudConfig(...(config('crudstrap.themes.' . trim($this->argument('theme')))));
        $this->config = $config->override($crudJson);
        $this->crud = new CrudFile($crudJson, $this->config, $this->getNameInput());
        parent::handle();
    }
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'crud:resource
                            {name : The name of the transformer.}
							{theme : Config theme for this crud.}
                            {crud : Crud json file.}
                            {--f|force : Force delete.}
							{--collection= : create a collection.}';




    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->collection()
            ? $this->resolveStubPath('/stubs/resource-collection.stub')
            : (config('crudstrap.custom_template')
                ? config('crudstrap.path') . '/resource.stub'
                : __DIR__ . '/../stubs/resource.stub');
    }





    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        return $this->replaceTransformArray($stub)
            ->replaceNamespace($stub, $name)
            ->replaceMorphsTrait($stub)
            ->replaceClass($stub, $name);
    }




    protected function replaceTransformArray(&$stub)
    {
        $transformArray = "";
        $fields = $this->crud->fields->map(function (Field $field) {
            $fieldName = $field->name();
            return "\t\t\t'{$fieldName}'=>\$this->{$fieldName},";
        });
        $relations = $this->crud->relations->map(fn (Relation $r) => $r->getResourceString());
        $transformArray = "[\n" . $fields->implode("\n") . "\n" . $relations->implode("\n") . "\n\t\t]";
        $stub = str_replace('{{transformArray}}', $transformArray, $stub);
        return $this;
    }

    protected function replaceMorphsTrait(&$stub)
    {
        $hasMorphs = $this->crud->relations->contains(fn (Relation $r) => $r->isMorph());
        $morphsTraitNamespace =  $hasMorphs ? 'use App\Traits\WhenMorphed;' : '';
        $morphsTrait = $hasMorphs ? 'use WhenMorphed;' : '';
        $stub = str_replace(['{{morphTraitNamespace}}', '{{morphTrait}}'], [$morphsTraitNamespace, $morphsTrait], $stub);
        return $this;
    }
}
