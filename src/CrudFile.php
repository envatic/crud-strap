<?php

namespace Envatic\CrudStrap;

use Envatic\CrudStrap\Fields\Field;
use Envatic\CrudStrap\Fields\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class CrudFile
{
    public bool $hasUuid;
    public Collection $fields;
    public Collection $relations;
    public Stringable $crud_name;
    function __construct(
        public object $crud,
        public CrudConfig $config,
        public string $name
    ) {
        $this->fields = collect($crud->fields)->map(function ($field) {
            return new Field($field, $this);
        });

        $this->relations = collect($crud->relationships)->map(function ($relationships) {
            return new Relation($relationships, $this);
        });
        $this->hasUuid = $this->fields->contains(fn (Field $f) => $f->type()->isUuid());
        $this->crud_name = str($name);
    }

    public function crudName()
    {
        return $this->crud_name->lower();
    }


    public function crudNameSingular()
    {
        return $this->crud_name->lower()->singular();
    }

    public function modelName()
    {
        return $this->crud_name->lower()->singular()->ucfirst();
    }

    public function viewName()
    {
        return $this->crud_name->snake('-')->ucfirst();
    }

    public function viewPath()
    {
        return $this->config->viewPath;
    }


    /**
     * get the table for migration;
     */
    function tableName(): string
    {
        $name = Str::of($this->name)->snake();
        return  $this->config->isPivot
            ? $name->singular()->value()
            : $name->plural()->value();
    }

    public function modelClass()
    {
        $namespace = $this->config->modelNamespace;
        $model = $this->modelName();
        return "{$namespace}\\$model";
    }




    function indexes()
    {
        return $this->crud->indexes ?? [];
    }
}
