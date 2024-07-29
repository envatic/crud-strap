<?php

namespace Envatic\CrudStrap\Fields;

use Envatic\CrudStrap\Crud;
use Illuminate\Support\Str;

class Relation
{
    public string $name;
    public ?string $type;
    public ?string $args;
    public function __construct(object $relation)
    {
        $this->name = $relation->name;
        $this->type = $relation->type;
        $this->args = $relation->class ?? null;
    }

    /**
     *  The name of this relationship
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The args used to define the model relation
     */
    protected function parseArgs()
    {
        $args = Str::of($this->args)->explode('|');
        $className = $args->shift();
        $fullClass = Str::ucfirst($className) . "::class";
        return Crud::parseFunctionParams($fullClass . "," . $args->implode(','));
    }

    /**
     *  The type of this relationship eg belongsTo
     */
    function relationType(): string
    {
        $mods = Str::of($this->type)->explode('|');
        return $mods->shift();
    }

    /**
     *  related model name
     */
    function relationModel(): string
    {
        $mods = Str::of($this->args)->explode('|');
        $model = $mods->shift();
        return Str::ucfirst($model);
    }

    /**
     *  function code to define the relationship
     */
    function getFunctionString(): string
    {
        $mods = Str::of($this->type)->explode('|');
        $relation =  $mods->shift();
        $modifiers = $mods->map(function ($mod) {
            [$modifier, $paramList] = explode(':', $mod) + [null, null];
            $params = $paramList
                ? Crud::parseFunctionParams($paramList)
                : '';
            return "->{$modifier}({$params})";
        })->implode("");
        $ownership = Str::contains($relation, 'has') ? 'Owns' : "Belongs To";
        $args = static::parseArgs();
        $relationClass = ucfirst($this->relationType());
        $code = "
    /**\n
    * Get the {$this->name} this model {$ownership}.
    *
    */
    ";
        $code .= "public function {$this->name}():{$relationClass}\n\t{\n\t\treturn \$this->{$relation}(" . $args . ")" . $modifiers . ";"
            . "\n\t}";
        return $code;
    }

    /**
     *  the relationship function return class;
     */
    function getModelImport()
    {
        $relation = ucfirst($this->relationType());
        return "use Illuminate\\Database\\Eloquent\\Relations\\{$relation}";
    }

    /**
     * Define the relation for Resources file
     */
    function getResourceString()
    {
        $type = $this->relationType();
        $resource  = $this->relationModel();
        if (is_null($type)) return null;
        if ($this->isMorph())
            return "\t\t\t'{$this->name}'=> \$this->whenMorhphed('{$this->name}'),";
        return  Str::contains($type, 'Many')
            ? "\t\t\t'{$this->name}'=> {$resource}::collection(\$this->whenLoaded('{$this->name}')),"
            : "\t\t\t'{$this->name}'=> new {$resource}(\$this->whenLoaded('{$this->name}')),";
    }

    /**
     * if this relation is a morphto Relation
     */
    function isMorph()
    {
        $this->relationType() == 'morphTo';
    }
}
