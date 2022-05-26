<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudTransformerCommand extends GeneratorCommand
{


    protected $signature = 'crud:transformer
                            {name : The name of the transformer.}
                            {--model= : The name of the model.}
                            {--f|force : Force delete.}
							{--transform-array= : The names of the Transformed array.}
							{--model-namespace= : The namespace of the Model.}
                            {--relationships= : The relationships for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new transformer.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Transformer';


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudstrap.custom_template')
            ? config('crudstrap.path') . '/transformer.stub'
            : __DIR__ . '/../stubs/transformer.stub';
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
        return $rootNamespace . '\\' . 'Transformers';
    }


    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $modelname = $this->option('model') ?: $this->argument('name');
        $modelnamevariable  = strtolower($modelname);
        $modelNamespace = $this->option('model-namespace');
        $availableIncludes = "";
        $transformArray = $this->option("transform-array");
        $relationships = trim($this->option('relationships')) != '' ? explode(';', trim($this->option('relationships'))) : [];
        $ret = $this->replaceNamespace($stub, $name)
            ->replaceModelName($stub, $modelname)
            ->replaceModelNamespace($stub, $modelNamespace)
            ->replaceModelnamevariable($stub, $modelnamevariable)
            ->replaceTransformArray($stub, $transformArray);
        if (count($relationships)) {
            $aIncludes = [];
            foreach ($relationships as $rel) {
                $parts = explode('#', $rel);
                $relationshipName = $parts[0];
                if (count($parts) != 3) {
                    continue;
                }
                $args = explode('|', trim($parts[2]));
                $rl = explode('\\', $args[0]);
                $relationshipModel = end($rl);
                $relatedTransformer = '\\App\\Transformers\\' . $relationshipModel . 'Transformer';
                $ret->createRelationshipFunction($stub, $relationshipName, $modelname, $modelnamevariable, $relatedTransformer);
                $aIncludes[] = "'" . $relationshipName . "'";
            }
            $availableIncludes = 'protected $availableIncludes = [' . implode(',', $aIncludes) . "];
	/**
     * List of resources to automatically include
     *
     * @var array
     */
    protected \$defaultIncludes = [ ]";
        }


        $ret->replaceRelationshipPlaceholder($stub)
            ->replaceAvailableIncludes($stub, $availableIncludes);
        return $ret->replaceClass($stub, $name);
    }

    protected function replaceModelnamevariable(&$stub, $modelnamevariable)
    {

        $stub = str_replace('{{modelNameVariable}}', $modelnamevariable, $stub);
        return $this;
    }


    protected function replaceTransformArray(&$stub, $transformArray)
    {
        $stub = str_replace('{{transformArray}}', $transformArray, $stub);

        return $this;
    }

    protected function replaceAvailableIncludes(&$stub, $availableIncludes)
    {
        $stub = str_replace('{{availableIncludes}}', $availableIncludes, $stub);
        return $this;
    }



    /**
     * Create the code for a included Models
     *
     * @param string $stub
     * @param string $relationshipName  the name of the function, e.g. owners
     * @param string $relationshipType  the type of the relationship, hasOne, hasMany, belongsTo etc
     * @param array $relationshipArgs   args for the relationship function
     */
    protected function createRelationshipFunction(&$stub, $relationshipName, $modelname, $modelnamevariable, $relatedTransformer)
    {

        $tabIndent = '    ';
        $code = "public function include" . ucfirst($relationshipName) . "( " . $modelname . " $" . $modelnamevariable . " )\n" . $tabIndent . "{\n" . $tabIndent . $tabIndent
            . "return \$this->item( $" . $modelnamevariable . "->" . $relationshipName . ", new " . $relatedTransformer . ");"
            . "\n" . $tabIndent . "}";

        $str = '{{relationshipFunctions}}';
        $stub = str_replace($str, $code . "\n" . $tabIndent . $str, $stub);
        return $this;
    }

    protected function replaceRelationshipPlaceholder(&$stub)
    {
        $stub = str_replace('{{relationshipFunctions}}', '', $stub);
        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace('{{modelName}}', $modelName, $stub);

        return $this;
    }

    /**
     * Replace the modelNamespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelNamespace
     *
     * @return $this
     */
    protected function replaceModelNamespace(&$stub, $modelNamespace)
    {
        $stub = str_replace('{{modelNamespace}}', $modelNamespace, $stub);

        return $this;
    }
}
