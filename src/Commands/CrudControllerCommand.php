<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\Crud;
use Envatic\CrudStrap\CrudConfig;
use Envatic\CrudStrap\CrudFile;
use Envatic\CrudStrap\Fields\Field;
use Envatic\CrudStrap\Fields\Relation;
use Illuminate\Support\Str;

class CrudControllerCommand extends BaseCrud
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller
                            {name : The name of the controler.}
                            {theme : Config theme for this crud.}
                            {crud : Crud json file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';




    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . '/inertia-controller.stub'
            : __DIR__ . '/../stubs/inertia-controller.stub';
    }



    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $base = $rootNamespace . '\Http\Controllers';
        $namespace = $this->config->controllerNamespace;
        if (!empty($namespace)) return $base . '\\' . $namespace;
        return $base;
    }


    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name . 'Controller';
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }


    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        if ($this->config->force) {
            return false;
        }
        return parent::alreadyExists($rawName);
    }


    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        return $this->replaceNamespace($stub, $name)
            ->replaceRelationsList($stub)
            ->replaceViewPath($stub)
            ->replaceViewName($stub)
            ->replaceCrudName($stub)
            ->replaceCrudNameSingular($stub)
            ->replaceCrudNameCaps($stub)
            ->replaceModelName($stub)
            ->replaceModelNamespace($stub)
            ->replaceRouteGroup($stub)
            ->replaceRoutePrefix($stub)
            ->replaceValidationRules($stub)
            ->replacePaginationNumber($stub)
            ->replaceFileSnippet($stub)
            ->replaceWhereSnippet($stub)
            ->replaceSaveable($stub)
            ->replaceImports($stub)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the viewPath for the given stub.
     *
     * @param  string  $stub
     *
     * @return $this
     */
    protected function replaceViewPath(&$stub)
    {
        $stub = str_replace('{{viewPath}}', $this->config->viewPath ?? "", $stub);
        return $this;
    }


    /**
     * Replace the viewName fo the given stub.
     *
     * @param string $stub
     *
     * @return $this
     */
    protected function replaceViewName(&$stub)
    {
        $viewName = $this->crud->viewName();
        $stub = str_replace('{{viewName}}', $viewName, $stub);
        return $this;
    }



    /**
     * Replace the crudName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $crudName
     *
     * @return $this
     */
    protected function replaceCrudName(&$stub)
    {
        $crudName = $this->crud->crudName();
        $stub = str_replace('{{crudName}}', $crudName, $stub);

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param  string  $stub
     * @param  string  $crudNameSingular
     *
     * @return $this
     */
    protected function replaceCrudNameSingular(&$stub)
    {
        $crudNameSingular = $this->crud->crudNameSingular();
        $stub = str_replace('{{crudNameSingular}}', $crudNameSingular, $stub);
        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub)
    {
        $modelName = $this->crud->modelName();
        $stub = str_replace('{{modelName}}', $modelName, $stub);
        return $this;
    }

    /**
     * Replace the CrudNameCaps for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelName
     *
     * @return $this
     */

    protected function replaceCrudNameCaps(&$stub)
    {
        $crudNameCaps = $this->crud->crud_name->lower()->ucfirst();
        $stub = str_replace('{{crudNameCaps}}', $crudNameCaps, $stub);
        return $this;
    }

    /**
     * Replace the modelNamespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelNamespace
     *
     * @return $this
     */
    protected function replaceModelNamespace(&$stub)
    {
        $modelNamespace = $this->config->modelNamespace;
        $stub = str_replace('{{modelNamespace}}', $modelNamespace, $stub);
        return $this;
    }

    /**
     * Replace the routeGroup for the given stub.
     *
     * @param  string  $stub
     * @param  string  $routeGroup
     *
     * @return $this
     */
    protected function replaceRouteGroup(&$stub)
    {
        $routeGroup = $this->config->routeGroup
            ? $this->config->routeGroup . '/'
            : '';
        $stub = str_replace('{{routeGroup}}', $routeGroup, $stub);

        return $this;
    }



    /**
     * Replace the routePrefix for the given stub.
     *
     * @param  string  $stub
     * @param  string  $routePrefix
     *
     * @return $this
     */
    protected function replaceRoutePrefix(&$stub)
    {
        $routePrefix = $this->config->routeGroup
            ? $this->config->routeGroup . '.'
            : '';
        $stub = str_replace('{{routePrefix}}', $routePrefix, $stub);

        return $this;
    }





    /**
     * Replace the validationRules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $validationRules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub)
    {
        $validationRules = "";
        $rules = $this->crud->fields->map(function (Field $field) {
            if (!$field->rules()) return null; // skip empty rules
            return  $field->validation();
        })->filter();
        if ($rules->count()) {
            $validationRules = "\$request->validate([";
            $validationRules .=  $rules->implode("");
            $validationRules .= "\n\t\t]);";
        }
        $stub = str_replace('{{validationRules}}', $validationRules, $stub);
        return $this;
    }

    /**
     * Replace the pagination placeholder for the given stub
     *
     * @param $stub
     * @param $perPage
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub)
    {
        $pagination = $this->config->pagination;
        $stub = str_replace('{{pagination}}', $pagination, $stub);
        return $this;
    }

    /**
     * Replace the file snippet for the given stub
     *
     * @param $stub
     * @param $fileSnippet
     *
     * @return $this
     */
    protected function replaceFileSnippet(&$stub)
    {
        $fileSnippet = "";
        $uploads = $this->crud
            ->fields
            ->map(fn (Field $f) => $f->useFileUploadClass())
            ->filter();
        if ($uploads->count()) {
            $fileSnippet = $uploads->implode("\n\t\t");
        }
        $stub = str_replace('{{fileSnippet}}', $fileSnippet, $stub);
        return $this;
    }

    /**
     * Replace class imports
     *
     * @param $stub
     * @param $fileSnippet
     *
     * @return $this
     */
    protected function replaceImports(&$stub)
    {
        $useItems = "";
        $imports = $this->crud
            ->fields
            ->map(fn (Field $f) => $f->includeFileUploadClass() ?? $f->includeCastClasses())
            ->filter();
        if ($imports->count()) {
            $useItems = $imports->implode("\n");
        }
        $stub = str_replace('{{useItems}}', $useItems, $stub);
        return $this;
    }

    /**
     * Replace the where snippet for the given stub
     *
     * @param $stub
     * @param $whereSnippet
     *
     * @return $this
     */
    protected function replaceWhereSnippet(&$stub)
    {
        $whereSnippet = $this->crud->fields->map(
            fn (Field $f, $index) => ($index == 0)
                ? "where('{$f->name()}', 'LIKE', \"%\$keyword%\")"
                : "->orWhere('{$f->name()}', 'LIKE', \"%\$keyword%\")"
        );
        $stub = str_replace('{{whereSnippet}}', $whereSnippet->implode("\n\t\t\t"), $stub);
        return $this;
    }



    protected function replaceSaveable(&$stub)
    {
        $crudNameSingular = $this->crud->crudNameSingular();
        $saveable = $this->crud->fields->map(
            fn (Field $f) => "\${$crudNameSingular}->{$f->name()} = \$request->{$f->name()};"
        )->implode("\n\t\t");
        $saveable .= "\n\t\t$" . $crudNameSingular . "->save();";
        $stub = str_replace('{{saveable}}', $saveable, $stub);
        return $this;
    }


    protected function replaceRelationsList(&$stub)
    {
        $relationsList = "";
        $relationsLoadList = "";
        if ($this->crud->relations->count()) {
            $list = $this->crud->relations
                ->map(fn (Relation $r) => $r->name())
                ->implode(",");
            $parsedList = Crud::parseFunctionParams($list);
            $relationsList = "->with([{$parsedList}])";
            $relationsLoadList = '${{crudNameSingular}}->load([' . $parsedList . ']);';
        }
        $stub = str_replace('{{relationsList}}', $relationsList, $stub);
        $stub = str_replace('{{relationsLoadList}}', $relationsLoadList, $stub);
        return $this;
    }
}
