<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Foundation\Console\ResourceMakeCommand;

class CrudResourceCommand extends ResourceMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'crud:resource
                            {name : The name of the transformer.}
							{--resource-array= : The names of the Transformed array.}
							{--has-morphs : Enable the morphable triat.}
                            {--f|force : Force delete.}
							{--collection= : create a collection.}';




    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudstrap.path') . '/resource.stub';
    }





    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $transformArray = $this->option("resource-array");
        $morphsTraitNamespace = $this->option("has-morphs")
            ? 'use Envatic\CrudStrap\Traits\WhenMorphed;'
            : '';
        $morphsTrait = $this->option("has-morphs")
            ? 'use WhenMorphed;'
            : '';
        return $this->replaceTransformArray($stub, $transformArray)
            ->replaceNamespace($stub, $name)
            ->replaceMorphsTraitNamespace($stub, $morphsTraitNamespace)
            ->replaceMorphsTrait($stub, $morphsTrait)
            ->replaceClass($stub, $name);
    }




    protected function replaceTransformArray(&$stub, $transformArray)
    {
        $stub = str_replace('{{transformArray}}', $transformArray, $stub);

        return $this;
    }

    protected function replaceMorphsTraitNamespace(&$stub, $morphsTraitNamespace)
    {
        $stub = str_replace('{{morphTraitNamespace}}', $morphsTraitNamespace, $stub);

        return $this;
    }
    protected function replaceMorphsTrait(&$stub, $morphsTrait)
    {
        $stub = str_replace('{{morphTrait}}', $morphsTrait, $stub);
        return $this;
    }
}
