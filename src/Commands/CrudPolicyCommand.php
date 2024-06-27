<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Foundation\Console\PolicyMakeCommand;

class CrudPolicyCommand extends PolicyMakeCommand
{

    protected function getStub()
    {
        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . '/policy.stub'
            : __DIR__ . '/../stubs/policy.stub';
    }
}
