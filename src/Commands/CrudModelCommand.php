<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
class CrudModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {--table= : The name of the table.}
                            {--fillable= : The names of the fillable columns.}
                            {--relationships= : The relationships for the model}
                            {--casts= : The field casts of the model}
                            {--use= : Imported Classes of the model}
                            {--use-trait= : Imported Traits of the model}
                            {--pk=id : The name of the primary key.}
                            {--f|force : Force delete.}
                            {--soft-deletes : Include soft deletes fields.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $name = $this->argument('name');
        $modelName = Str::of($name)->classBasename();
        $stub = 'model.stub';
        if ($modelName == 'User') {
            $stub = config('crudstrap.user_teams_model')
            ? 'UserWithTeams.stub'
            : 'user.stub';
        }
        return config('crudstrap.custom_template')
        ? config('crudstrap.path') . '/' . $stub
            : __DIR__ . '/../stubs/' . $stub;
    }
    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {

        $stub = $this->files->get($this->getStub());
        $table = $this->option('table') ?: $this->argument('name');
        $fillable = $this->option('fillable');
        $primaryKey = $this->option('pk');
        $use = str_replace(';', ";\n", $this->option('use'));
        $useTrait = str_replace(';', ";\n", $this->option('use-trait'));
        $relationships = trim($this->option('relationships')) != '' ? explode(';', trim($this->option('relationships'))) : [];
        $casts = trim($this->option('casts')) != '' ? explode(';', trim($this->option('casts'))) : [];
        $softDeletes = $this->option('soft-deletes');
        if (!empty($primaryKey)) {
            $primaryKey = <<<EOD
/**
    * The database primary key value.
    *
    * @var string
    */
    protected \$primaryKey = '$primaryKey';
EOD;
        }

        $ret = $this->replaceNamespace($stub, $name)
            ->replaceTable($stub, $table)
            ->replaceFillable($stub, $fillable)
            ->replacePrimaryKey($stub, $primaryKey)
            ->replaceSoftDelete($stub, $softDeletes)
            ->replaceData($stub, $casts, $use, $useTrait);
        foreach ($relationships as $rel) {
            $parts = explode('#', $rel);
            if (count($parts) != 3) {
                continue;
            }

            // blindly wrap each arg in single quotes
            $args = explode('|', trim($parts[2]));
            $argsString = "";
            if (trim($args[0]) != '') {
                $argsString .=  $args[0] . '::class, ';
                unset($args[0]);
            }
            foreach ($args as $k => $v) {
                if (trim($v) == '') {
                    continue;
                }
                $argsString .= "'" . trim($v) . "', ";
            }

            $argsString = substr($argsString, 0, -2);
            // remove last comma
            $ret->createRelationshipFunction($stub, trim($parts[0]), trim($parts[1]), $argsString);
        }

        $ret->replaceRelationshipPlaceholder($stub);
        return $ret->replaceClass($stub, $name);
    }

    /**
     * Create the code for a model relationship
     *
     * @param string $stub
     * @param string $relationshipName  the name of the function, e.g. owners
     * @param string $relationshipType  the type of the relationship, hasOne, hasMany, belongsTo etc
     * @param array $relationshipArgs   args for the relationship function
     */
    protected function createRelationshipFunction(&$stub, $relationshipName, $relationshipType, $argsString)
    {
        $mods = explode('|', $relationshipType);
        $relationshipTypeName =  array_shift($mods);
        $modifiers = "";
        if (count($mods) > 0) {
            foreach ($mods as $mod) {
                $varString = explode(':', $mod);
                $modname =  array_shift($varString);
                $var = $varString[0] ?? null;
                $modifiers .= "->" . $modname . "(" .  $var . ")";
            }
        }
        $name = Str::contains($relationshipTypeName,'has')?'Owns':"Belongs To";
        $tabIndent = '    ';
        $item = Str::of($this->argument('name'))->singular()->lower()->explode('\\')->pop();
        $code = "
    /**\n
    * Get the {$relationshipName} the {$item} {$name}.
    *
    * @return \Illuminate\Database\Eloquent\Relations\\{$relationshipTypeName}
    */
    ";
        $code .= "public function " . $relationshipName . "()\n" . $tabIndent . "{\n" . $tabIndent . $tabIndent
            . "return \$this->" . $relationshipTypeName . "(" . $argsString . ")" . $modifiers . ";"
            . "\n" . $tabIndent . "}";

        $str = '{{relationships}}';
        $stub = str_replace($str, $code . "\n" . $tabIndent . $str, $stub);
        return $this;
    }

    /**
     * Create the code for a model relationship
     *
     * @param string $stub
     * @param string $relationshipName  the name of the function, e.g. owners
     * @param string $relationshipType  the type of the relationship, hasOne, hasMany, belongsTo etc
     * @param array $relationshipArgs   args for the relationship function
     */
    protected function replaceData(&$stub,  $casts_array, $use, $useTrait)
    {
        $castStr = "";
        if (count($casts_array)) {
            $tabIndent = '    ';
            $castStr = "protected \$casts = [\n";
            foreach ($casts_array as $cast) {

                $mods = explode('#', $cast);
                if (empty($mods[0])) continue;
                $castStr .= $tabIndent . $tabIndent . "'" . $mods[0] . "' => " . $mods[1] . ", \n";
            }
            $castStr .= $tabIndent . '];';
        }
        $replace = [
            '{{casts}}' => $castStr,
            '{{use}}' => $use,
            '{{useTrait}}' => $useTrait,
        ];
        $stub = str_replace(
            array_keys($replace),
            array_values($replace),
            $stub
        );
        return $this;
    }

    /**
     * Replace the (optional) soft deletes part for the given stub.
     *
     * @param  string  $stub
     * @param  string  $replaceSoftDelete
     *
     * @return $this
     */
    protected function replaceSoftDelete(&$stub, $replaceSoftDelete)
    {
        if ($replaceSoftDelete) {
            $stub = str_replace('{{softDeletes}}', "use SoftDeletes;\n    ", $stub);
            $stub = str_replace('{{useSoftDeletes}}', "use Illuminate\Database\Eloquent\SoftDeletes;\n", $stub);
        } else {
            $stub = str_replace('{{softDeletes}}', '', $stub);
            $stub = str_replace('{{useSoftDeletes}}', '', $stub);
        }

        return $this;
    }
    















    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    
    /**
     * Replace the table for the given stub.
     *
     * @param  string  $stub
     * @param  string  $table
     *
     * @return $this
     */
    protected function replaceTable(&$stub, $table)
    {
        $stub = str_replace('{{table}}', $table, $stub);

        return $this;
    }

    /**
     * Replace the fillable for the given stub.
     *
     * @param  string  $stub
     * @param  string  $fillable
     *
     * @return $this
     */
    protected function replaceFillable(&$stub, $fillable)
    {
        $stub = str_replace('{{fillable}}', $fillable, $stub);

        return $this;
    }

    /**
     * Replace the primary key for the given stub.
     *
     * @param  string  $stub
     * @param  string  $primaryKey
     *
     * @return $this
     */
    protected function replacePrimaryKey(&$stub, $primaryKey)
    {
        $stub = str_replace('{{primaryKey}}', $primaryKey, $stub);

        return $this;
    }

    
    /**
     * remove the relationships placeholder when it's no longer needed
     *
     * @param $stub
     * @return $this
     */
    protected function replaceRelationshipPlaceholder(&$stub)
    {
        $stub = str_replace('{{relationships}}', '', $stub);
        return $this;
    }
}
