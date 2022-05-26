<?php

namespace Envatic\CrudStrap\Commands;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class CrudEnumCommand extends GeneratorCommand
{


    protected $signature = 'crud:enum
                            {name : The name of the model.}
                            {--model= : The name of the model.}
                            {--f|force : Force delete.}
                            {--enum= : The Field Data.}';

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
        $data = str_replace('options=', '', $this->option('enum'));
        $name = $this->argument('name');
        $enum = class_basename(Str::ucfirst($name));
        $namespace = 'App\\Enums';
        $cases = "";
        $fields = json_decode($data, true);
        foreach ($fields as $key => $field) {
            $name = Str::of($key)->replace(['-', '_', ':'], "_")->upper();
            $value = $key;
            $cases .= "\tcase {$name} = '{$value}';\n";
        }
        $replace = [
            '{{ enumNamespace }}' => $namespace,
            '{{ enum }}' => $enum,
            '{{enum}}' => $enum,
            '{{cases}}' => $cases
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            $stub
        );
    }
}
