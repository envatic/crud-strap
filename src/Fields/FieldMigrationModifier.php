<?php

namespace Envatic\CrudStrap\Fields;

use Envatic\CrudStrap\Crud;


class FieldMigrationModifier
{
    public string $func;
    public ?string $params;
    public $modifierLookup = [
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


    function __construct(public string $modifier)
    {
        [$func, $parameter] = explode(':', $modifier, 2) + [null, null];
        $this->func = $func;
        $this->params = $parameter;
    }

    public function toMigration(): string
    {

        if (!in_array($this->func, $this->modifierLookup)) return "";
        $list = $this->params
            ? Crud::parseFunctionParams($this->params)
            : "";
        return  "->{$this->func}($list)";
    }
}
