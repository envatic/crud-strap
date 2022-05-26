<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class CrudControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller
                            {name : The name of the controler.}
                            {--crud-name= : The name of the Crud.}
                            {--model-name= : The name of the Model.}
                            {--model-namespace= : The namespace of the Model.}
                            {--controller-namespace= : Namespace of the controller.}
                            {--view-path= : The name of the view path.}
                            {--fields= : Field names for the form & migration.}
                            {--relationships= : RelationShips to Load Releated Models}
                            {--validations= : Validation rules for the fields.}
                            {--route-group= : Prefix of the route group.}
                            {--pagination=25 : The amount of models per page for index pages.}
                            {--force : Overwrite already existing controller.}
							{--api : use api controller.}
							{--inertia : use inertia controller.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $api = $this->option('api');
        $inertia = $this->option('inertia');
        if (!$api && !$inertia) return $this->getNormalStub();
        $stub = $inertia ? 'inertia' : 'api';
        return config('crudstrap.custom_template')
        ? config('crudstrap.path') . '/' . $stub . '-controller.stub'
        : __DIR__ . '/../stubs/' . $stub . '-controller.stub';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getNormalStub()
    {
        return config('crudstrap.custom_template')
        ? config('crudstrap.path') . '/controller.stub'
        : __DIR__ . '/../stubs/controller.stub';
    }

    /**
     * Replace the javascript validationRules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $validationRules
     *
     * @return $this
     */
    protected function replaceJSValidation(&$stub, $jsvalidation)
    {
        $stub = str_replace('{{jsvalidator}}', $jsvalidation, $stub);
        return $this;
    }

    protected function replaceSaveable(&$stub, $saveable)
    {
        $stub = str_replace('{{saveable}}', $saveable, $stub);
        return $this;
    }


    protected function replaceRelatedModels(&$stub, $relatedModels, $relatedModelsItems)
    {
        $stub = str_replace(['{{relatedModels}}', '{{relatedModelsItems}}'], [$relatedModels, $relatedModelsItems], $stub);
        return $this;
    }


    protected function replaceCrudNameCaps(&$stub, $crudNameCaps)
    {
        $stub = str_replace('{{crudNameCaps}}', $crudNameCaps, $stub);
        return $this;
    }
    protected function replaceUseItems(&$stub, $useItems)
    {
        $stub = str_replace('{{useItems}}', $useItems, $stub);
        return $this;
    }
    protected function replaceRelationsList(&$stub, $relationsList)
    {
        $stub = str_replace('{{relationsList}}', $relationsList, $stub);
        return $this;
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
        return $rootNamespace . '\\' . ($this->option('controller-namespace') ? $this->option('controller-namespace') : 'Http\Controllers');
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        if ($this->option('force')) {
            return false;
        }
        return parent::alreadyExists($rawName);
    }
    protected function buildClass($name)
    {
        $cname = Str::of($this->option('crud-name'));
        $stub = $this->files->get($this->getStub());
        $view = $this->option('view-path');
        $crudName = $cname->lower();
        $crudNameCaps = $cname->lower()->ucfirst();
        $crudNameSingular = $crudName->singular();
        $modelName = $this->option('model-name');
        $relationships = $this->option('relationships');
        $modelNamespace = $this->option('model-namespace');
        $modelNamespaced = $this->option('model-namespace');
        $routeGroup = ($this->option('route-group')) ? $this->option('route-group') . '/' : '';
        $routePrefix = ($this->option('route-group')) ? $this->option('route-group') : '';
        $routePrefixCap = ucfirst($routePrefix);
        $perPage = intval($this->option('pagination'));
        $viewName = $cname->snake('-')->ucfirst();
        $routeViewName = $viewName->lower();
        $fields = $this->option('fields');
        $validations = rtrim($this->option('validations'), ';');
        $viewPath = $view ? Str::of($view)->ucfirst() . "/" : "";
        $validationRules = '';
        $jsvalidation = '';
        $relationsListItems = '';
        $relationsList = '';
        $saveable = '';
        $useItems = "";
        if (trim($relationships)) {
            $relations = explode(';', $relationships);
            foreach ($relations as $r) {
                if (trim($r) == '') continue;
                $parts = explode('#', $r);
                $xname = trim($parts[0]);
                $relationsListItems .= ",'{$xname}'";
            }
            $relationsListItems = trim($relationsListItems, ',');
        }

        if ($relationsListItems != '') {
            $relationsList = "with([{$relationsListItems}])->";
        }
        if (trim($validations) != '') {
            $validationRules = "\$request->validate([";
            $rules = explode(';', $validations);
            $xrules = '';
            foreach ($rules as $v) {
                if (trim($v) == '') continue;
                // extract field name and args
                $parts = explode('#', $v);
                $fieldName = trim($parts[0]);
                $rules = trim($parts[1]);
                $xrules .= "\n\t\t\t'$fieldName' => '$rules',";
                $saveable .= "\n\t\t$" . "{$crudNameSingular}->{$fieldName} = \$request->{$fieldName};";
            }
            $validationRules .= substr($xrules, 0, -1); // lose the last comma
            $validationRules .= "\n\t\t]);";
            $jsvalidation    = "\$jsvalidator = JsValidator::make([";
            $jsvalidation .= substr($xrules, 0, -1); // lose the last comma again
            $jsvalidation .= "\n\t\t]);";
            $saveable .= "\n\t\t$" . $crudNameSingular . "->save();";
        }



        if (\App::VERSION() < '5.3') {
            $snippet = <<<EOD
        if (\$request->hasFile('{{fieldName}}')) {
            \$file = \$request->file('{{fieldName}}');
            \$fileName = Str::random(40) . '.' . \$file->getClientOriginalExtension();
            \$destinationPath = storage_path('/app/public/uploads');
            \$file->move(\$destinationPath, \$fileName);
            \$requestData['{{fieldName}}'] = 'uploads/' . \$fileName;
        }
EOD;
        } else {
            $snippet = <<<EOD
        if (\$request->hasFile('{{fieldName}}')) {
            \$requestData['{{fieldName}}'] = \$request->file('{{fieldName}}')
                ->store('uploads', 'public');
        }
EOD;
        }


        $fieldsArray = explode(';', $fields);
        $fileSnippet = '';
        $whereSnippet = '';
        $relatedModels = "";
        $relatedModelsItems = "";
        if ($fields) {
            $x = 0;
            foreach ($fieldsArray as $index => $item) {
                $itemArray = explode('#', $item);
                // some fields are just for db only eg rememberToken
                if (empty($itemArray[0])) continue;
                // remove migration modifiers string:16|nullable , 
                if (Str::contains($itemArray[1], ':') || Str::contains($itemArray[1], '|')) {
                    $types = explode('|', str_replace(':', "|", $itemArray[1]));
                    $type = $types[0];
                } else {
                    $type = $itemArray[1];
                }

                if ((trim($type) != 'select' || trim($type) != 'enum') &&  trim($type) == 'foreignId' && isset($itemArray[2])) {
                    $relmod = Str::of(str_replace("options=", "", $itemArray[2]))->ucfirst();
                    $useItems .= "\nuse App\\{$modelNamespaced}{$relmod};";
                    $useItems .= "\nuse App\\Http\\Resources\\{$relmod} as {$relmod}Resource;";
                    $relatedModels .= "\${$relmod->lower()}s = {$relmod}Resource::collection($relmod::all());";
                    $relatedModelsItems .= ",'{$relmod->lower()}s'";
                }
                if (trim($type) == 'file') {
                    $fileSnippet .= str_replace('{{fieldName}}', trim($itemArray[0]), $snippet) . "\n";
                }
                $fieldName = trim($itemArray[0]);
                $whereSnippet .= ($index == 0) ? "where('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n                " : "->orWhere('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n                ";
            }
            $whereSnippet .= "->";
        }
        return $this->replaceNamespace($stub, $name)
            ->replaceViewPath($stub, $viewPath)
            ->replaceViewName($stub, $viewName)
            ->replaceCrudName($stub, $crudName)
            ->replaceCrudNameSingular($stub, $crudNameSingular)
            ->replaceCrudNameCaps($stub, $crudNameCaps)
            ->replaceModelName($stub, $modelName)
            ->replaceModelNamespace($stub, $modelNamespace)
            ->replaceModelNamespaceSegments($stub, $modelNamespace)
            ->replaceRouteGroup($stub, $routeGroup)
            ->replaceRoutePrefix($stub, $routePrefix)
            ->replaceRoutePrefixCap($stub, $routePrefixCap)
            ->replaceValidationRules($stub, $validationRules)
            ->replaceJSValidation($stub, $jsvalidation)
            ->replaceRelatedModels($stub, $relatedModels, $relatedModelsItems)
            ->replacePaginationNumber($stub, $perPage)
            ->replaceFileSnippet($stub, $fileSnippet)
            ->replaceWhereSnippet($stub, $whereSnippet)
            ->replaceSaveable($stub, ltrim($saveable, "\n"))
            ->replaceRelationsList($stub, $relationsList)
            ->replaceUseItems($stub, $useItems)
            ->replaceClass($stub, $name);
    }


    /**
     * Replace the viewName fo the given stub.
     *
     * @param string $stub
     * @param string $viewName
     *
     * @return $this
     */
    protected function replaceViewName(&$stub, $viewName)
    {
        $stub = str_replace('{{viewName}}', $viewName, $stub);

        return $this;
    }

    /**
     * Replace the viewPath for the given stub.
     *
     * @param  string  $stub
     * @param  string  $viewPath
     *
     * @return $this
     */
    protected function replaceViewPath(&$stub, $viewPath)
    {
        $stub = str_replace('{{viewPath}}', $viewPath, $stub);

        return $this;
    }

    /**
     * Replace the crudName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $crudName
     *
     * @return $this
     */
    protected function replaceCrudName(&$stub, $crudName)
    {
        $stub = str_replace('{{crudName}}', $crudName, $stub);

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param  string  $stub
     * @param  string  $crudNameSingular
     *
     * @return $this
     */
    protected function replaceCrudNameSingular(&$stub, $crudNameSingular)
    {
        $stub = str_replace('{{crudNameSingular}}', $crudNameSingular, $stub);

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

    /**
     * Replace the modelNamespace segments for the given stub
     *
     * @param $stub
     * @param $modelNamespace
     *
     * @return $this
     */
    protected function replaceModelNamespaceSegments(&$stub, $modelNamespace)
    {
        $modelSegments = explode('\\', $modelNamespace);
        foreach ($modelSegments as $key => $segment) {
            $stub = str_replace('{{modelNamespace[' . $key . ']}}', $segment, $stub);
        }

        $stub = preg_replace('{{modelNamespace\[\d*\]}}', '', $stub);

        return $this;
    }

    /**
     * Replace the routePrefix for the given stub.
     *
     * @param  string  $stub
     * @param  string  $routePrefix
     *
     * @return $this
     */
    protected function replaceRoutePrefix(&$stub, $routePrefix)
    {
        $stub = str_replace('{{routePrefix}}', $routePrefix, $stub);

        return $this;
    }

    /**
     * Replace the routePrefixCap for the given stub.
     *
     * @param  string  $stub
     * @param  string  $routePrefixCap
     *
     * @return $this
     */
    protected function replaceRoutePrefixCap(&$stub, $routePrefixCap)
    {
        $stub = str_replace('{{routePrefixCap}}', $routePrefixCap, $stub);

        return $this;
    }

    /**
     * Replace the routeGroup for the given stub.
     *
     * @param  string  $stub
     * @param  string  $routeGroup
     *
     * @return $this
     */
    protected function replaceRouteGroup(&$stub, $routeGroup)
    {
        $stub = str_replace('{{routeGroup}}', $routeGroup, $stub);

        return $this;
    }

    /**
     * Replace the validationRules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $validationRules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $validationRules)
    {
        $stub = str_replace('{{validationRules}}', $validationRules, $stub);

        return $this;
    }

    /**
     * Replace the pagination placeholder for the given stub
     *
     * @param $stub
     * @param $perPage
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub, $perPage)
    {
        $stub = str_replace('{{pagination}}', $perPage, $stub);

        return $this;
    }

    /**
     * Replace the file snippet for the given stub
     *
     * @param $stub
     * @param $fileSnippet
     *
     * @return $this
     */
    protected function replaceFileSnippet(&$stub, $fileSnippet)
    {
        $stub = str_replace('{{fileSnippet}}', $fileSnippet, $stub);

        return $this;
    }

    /**
     * Replace the where snippet for the given stub
     *
     * @param $stub
     * @param $whereSnippet
     *
     * @return $this
     */
    protected function replaceWhereSnippet(&$stub, $whereSnippet)
    {
        $stub = str_replace('{{whereSnippet}}', $whereSnippet, $stub);

        return $this;
    }
}
