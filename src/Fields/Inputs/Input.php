<?php

namespace Envatic\CrudStrap\Fields\Inputs;

use Envatic\CrudStrap\Fields\Field;

abstract class Input
{
    public function __construct(public Field $field)
    {
    }
    public abstract function imports(): array;
    public abstract function render();
}
