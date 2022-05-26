<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Foundation\Console\ObserverMakeCommand;

class CrudObserverCommand extends ObserverMakeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:observer 
							{name : The name of the Crud.}
                            {--f|force : Force delete.}
                            {--model= : Name of the Model.}';


    protected function getStub()
    {
        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . '/observer.stub'
            : __DIR__ . '/../stubs/observer.stub';
    }
}
