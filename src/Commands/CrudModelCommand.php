<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\Fields\Field;
use Envatic\CrudStrap\Fields\Relation;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class CrudModelCommand extends BaseCrud
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {theme : Config theme for this crud.}
                            {crud : Crud json file.}
                            {--f|force : Force delete.}';

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
            $stub = 'user.stub';
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
        $this->replaceNamespace($stub, $name)
            ->replaceTable($stub)
            ->replaceFillable($stub)
            ->replacePrimaryKey($stub)
            ->replaceCasts($stub)
            ->replaceImports($stub)
            ->replaceTraits($stub)
            ->replaceRelationships($stub);
        return $this->replaceClass($stub, $name);
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
        $base = $rootNamespace . '\Models';
        $namespace = $this->config->modelNamespace;
        if (!empty($namespace)) return $base . '\\' . $namespace;
        return $base;
    }

    /**
     * generate relationship functions
     *
     * @param  string  $stub
     *
     * @return $this
     */

    public function replaceRelationships(&$stub)
    {
        $relations = $this->crud
            ->relations
            ->map(fn (Relation $relation) => $relation->getFunctionString());
        $code = $relations->implode("\n\t");
        $stub = str_replace('{{relationships}}', $code, $stub);
        return $this;
    }




    /**
     * Replace the table for the given stub.
     *
     * @param  string  $stub
     * @param  string  $table
     *
     * @return $this
     */
    protected function replaceTable(&$stub)
    {
        $table = $this->crud->tableName();
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
    protected function replaceFillable(&$stub)
    {
        $fillableStr = $this->crud->fields
            ->filter(fn (Field $f) => $f->isFillable())
            ->map(fn (Field $f) => $f->name())
            ->implode("',\n\t\t'");
        $fillable = "[\n        '" . $fillableStr . "'\n   ]";;
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
    protected function replacePrimaryKey(&$stub)
    {
        $primaryKey = $this->config->primaryKey;
        $primaryKeyBlock = <<<EOD
/**
    * The database primary key value.
    *
    * @var string
    */
    protected \$primaryKey = '$primaryKey';
EOD;
        $stub = str_replace('{{primaryKey}}', $primaryKeyBlock, $stub);

        return $this;
    }

    /**
     * Replace casts placeholder in the stub.
     * @param  string  $stub
     * @return $this
     */

    protected function replaceCasts(&$stub)
    {
        $castStr = "";
        $casts = $this->crud->fields->map(function (Field $field) {
            $cast = $field->cast();
            if (!$cast) return null;
            return "\t\t\t" .  $cast;
        })->filter();
        if ($casts->count()) {
            $castStr = "protected function casts() {\n\t\t return [\n";
            $castStr .= $casts->implode(",\n");
            $castStr .= "\n\t\t];\n\t}";
        }
        $stub =  str_replace('{{casts}}', $castStr, $stub);
        return $this;
    }

    /**
     * Replace use imports placeholder in the stub.
     * @param  string  $stub
     * @return $this
     */

    protected function replaceImports(&$stub)
    {
        $imports = "";
        if ($this->config->has('factory'))
            $imports .= "use Illuminate\Database\Eloquent\Factories\HasFactory; \n";
        if ($this->config->softdeletes)
            $imports .=  "use Illuminate\Database\Eloquent\SoftDeletes;\n";
        $imports .= $this->crud->fields
            ->map(fn (Field $f) => $f->includeCastClasses())
            ->filter()
            ->implode("\n");
        $imports .= "\n";
        if ($this->crud->hasUuid) {
            $imports .= "use App\\Traits\\HasUuid; \n";
        }
        if ($this->crud->relations->count()) {
            $imports .= $this->crud->relations
                ->map(fn (Relation $f) => $f->getModelImport())
                ->unique()
                ->implode(";\n");
            $imports .= ";\n";
        }
        $stub =  str_replace('{{use}}', $imports, $stub);
        return $this;
    }

    /**
     * Replace casts placeholder in the stub.
     * @param  string  $stub
     * @return $this
     */

    protected function replaceTraits(&$stub)
    {
        $traits = "";
        if ($this->config->softdeletes)
            $traits .=  "use SoftDeletes;\n";
        if ($this->crud->hasUuid) {
            $traits .= "use HasUuid; \n";
        }
        $stub =  str_replace('{{useTrait}}', $traits, $stub);
        return $this;
    }
}
