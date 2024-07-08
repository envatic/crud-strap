<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class CrudEnumCommand extends GeneratorCommand
{


    protected $signature = 'crud:enum
                            {name : The name of the model.}
                            {--cases= : The Field Data.}
                            {--int : Force Int return.}
                            {--f|force : Force delete.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Enum File.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Enum';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . '/enum.stub'
            : __DIR__ . '/../stubs/enum.stub';
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
        return $rootNamespace . '\\' . 'Enums';
    }


    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $cases = $this->option('cases');
        $is_int = $this->option('int');
        $name = $this->argument('name');
        $namespace = 'App\\Enums';
        $replace = [
            '{{type}}' => $is_int ? 'int' : 'string',
            '{{enumNamespace}}' => $namespace,
            '{{enum}}' => $name,
            '{{cases}}' => $cases
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            $stub
        );
    }
}
