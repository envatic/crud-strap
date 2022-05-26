<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Support\Str;

class CrudDbFactoryCommand extends FactoryMakeCommand
{
    protected $signature = 'crud:dbfactory
                            {name : The name of the Model.}
                            {--model : The name of the Model.}
                            {--faker= : The Faker Fields.}
                            {--f|force : Force delete.}
                            {--use= : The Faker Fields.}';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . 'factory.stub'
            : __DIR__ . '/../stubs/factory.stub';
    }
    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $fakerData = rtrim($this->option('faker'), ';');
        $use = str_replace(';', ";\n", $this->option('use'));
        $dataArray = explode(';', $fakerData);
        $faker = "";
        $randBytes = "\tpublic function randBytes()\n\t{\n\t\treturn random_bytes(16);\n\t}";
        $hash = "\tpublic  function hash()\n\t{\n\t\t\$pass = Str::random();\n\t\treturn Hash::make(\$pass);\n\t}";
        $fakeJson = " \tpublic  function fakeJson()\n\t{\n\t\treturn json_encode([\n\t\t\t'number' => \$this->faker->randomNumber(5, false),\n\t\t\t'date' => now(),\n\t\t]);\n\t}";
        $hasRandBytes = false;
        $hasFakeJson = false;
        $hasHash = false;
        foreach ($dataArray as $data) {
            $fields = explode('#', $data);
            $fieldName = $fields[0];
            if (empty($fieldName)) continue;
            $modifiers = explode('|', $fields[1]);
            $fakerFuncStr = array_shift($modifiers);
            if (Str::contains($fakerFuncStr, '(') && Str::contains($fakerFuncStr, ')')) {
                $faker .= "\t\t\t'{$fieldName}' => {$fakerFuncStr},\n";
                if (Str::contains(str_replace(' ', '', $fakerFuncStr), 'hash')) {
                    $hasHash = true;
                    $use .= "use Illuminate\Support\Facades\Hash;\nuse Illuminate\Support\Str;";
                    continue;
                }
                if (Str::contains(str_replace(' ', '', $fakerFuncStr), 'randBytes')) {
                    $hasRandBytes = true;
                    continue;
                }
                if (Str::contains(str_replace(' ', '', $fakerFuncStr), 'fakeJson')) {
                    $hasFakeJson = true;
                    continue;
                }
                continue;
            }
            $items = explode(':', $fakerFuncStr);
            $fakerFnc = $items[0];
            $funcVars = $items[1] ?? "";
            $mFuncs = collect($modifiers)->map(fn ($m) => "->{$m}()")->all();
            $modifierStr = count($mFuncs) > 0 ? implode("", $mFuncs) : "";
            $faker .= "\t\t\t'{$fieldName}' => \$this->faker{$modifierStr}->{$fakerFnc}({$funcVars}),\n";
        }

        $replace = [
            '{{faker}}' => $faker,
            '{{use}}' => rtrim($use),
            '{{randomBytes}}' => $hasRandBytes ? $randBytes : "",
            '{{hash}}' =>  $hasHash ? $hash : "",
            '{{fakeJson}}' => $hasFakeJson ? $fakeJson : "",
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }
}
