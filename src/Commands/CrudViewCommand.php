<?php

namespace Envatic\CrudStrap\Commands;

use Hamcrest\Arrays\IsArray;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:view
                            {name : The name of the Crud.}
                            {--fields= : The field names for the form.}
                            {--view-path= : The name of the view path.}
                            {--route-group= : Prefix of the route group.}
                            {--pk=id : The name of the primary key.}
                            {--validations= : Validation rules for the fields.}
                            {--form-helper=html : Helper for the form.}
                            {--custom-data= : Some additional values to use in the crud.}
                            {--localize : Localize the view? yes|no.}
                            {--inertia= : Use Inertia}
                            {--f|force : Force delete.}
							{--stub-path= : Optional name of the stubs folder in the view stubs dir.}';


    protected $emptyVueForm = '';
    protected $filledVueForm = '';
    protected $vars = [
        //'formFields',
        'formFieldsHtml',
        'varName',
        'crudName',
        'crudNameCap',
        'crudNameSingular',
        'primaryKey',
        'modelName',
        'modelNameCap',
        'viewName',
        'routePrefix',
        'routePrefixCap',
        'routeGroup',
        'formHeadingHtml',
        'formBodyHtml',
        'viewTemplateDir',
        'formBodyHtmlForShowView',
        'filledVueForm',
        'emptyVueForm',
        'propsHtml',
        'formBodyVueHtml'
    ];
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the Crud.';

    /**
     * View Directory Path.
     *
     * @var string
     */
    protected $viewDirectoryPath;


    /**
     *  Form field types collection.
     *
     * @var array
     */
    protected $typeLookup = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'radio' => 'radio',
        'boolean' => 'radio',
        'enum' => 'select',
        'select' => 'select',
        'file' => 'file',
        'uuid' => 'text',
        'bigIncrements' => 'number',
        'bigInteger' => 'number',
        'dateTimeTz' => 'datetime-local',
        'dateTime' => 'datetime-local',
        'foreignId' => 'number',
        'foreignIdFor' => 'number',
        'foreignUuid' => 'number',
        'geometryCollection' => 'texteara',
        'geometry' => 'texteara',
        'increments' => 'number',
        'ipAddress' => 'text',
        'jsonb' => 'texteara',
        'lineString' => 'text',
        'longText' => 'texteara',
        'macAddress' => 'text',
        'mediumIncrements' => 'number',
        'mediumInteger' => 'number',
        'mediumText' => 'number',
        'multiLineString' => 'text',
        'multiPoint' => 'text',
        'multiPolygon' => 'text',
        'nullableTimestamps' => 'datetime-local',
        'point' => 'text',
        'polygon' => 'text',
        'rememberToken' => 'text',
        'set' => 'text',
        'smallIncrements' => 'number',
        'smallInteger' => 'number',
        'timeTz' => 'datetime-local',
        'timestampTz' => 'datetime-local',
        'timestampsTz' => 'datetime-local',
        'tinyIncrements' => 'number',
        'tinyInteger' => 'number',
        'tinyText' => 'number',
        'unsignedBigInteger' => 'number',
        'unsignedDecimal' => 'number',
        'unsignedInteger' => 'number',
        'unsignedMediumInteger' => 'number',
        'unsignedSmallInteger' => 'number',
        'unsignedTinyInteger' => 'number',
        'uuid' => 'text',
        'year' => 'datetime-local',
    ];




    /**
     * Form's fields.
     *
     * @var array
     */
    protected $formFields = [];/**
     * Form's fields.
     *
     * @var array
     */
    protected $inertia = false;

    /**
     * Html of Form's fields.
     *
     * @var string
     */
    protected $formFieldsHtml = '';

    /**
     * Number of columns to show from the table. Others are hidden.
     *
     * @var integer
     */
    protected $defaultColumnsToShow = 3;

    /**
     * Variable name with first letter in lowercase
     *
     * @var string
     */
    protected $varName = '';

    /**
     * Name of the Crud.
     *
     * @var string
     */
    protected $crudName = '';

    /**
     * Crud Name in capital form.
     *
     * @var string
     */
    protected $crudNameCap = '';

    /**
     * Crud Name in singular form.
     *
     * @var string
     */
    protected $crudNameSingular = '';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Name of the Model.
     *
     * @var string
     */
    protected $modelName = '';

    /**
     * Name of the Model with first letter in capital
     *
     * @var string
     */
    protected $modelNameCap = '';

    /**
     * Name of the View Dir.
     *
     * @var string
     */
    protected $viewName = '';

    /**
     * Prefix of the route
     *
     * @var string
     */
    protected $routePrefix = '';

    /**
     * Prefix of the route with first letter in capital letter
     *
     * @var string
     */
    protected $routePrefixCap = '';

    /**
     * Name or prefix of the Route Group.
     *
     * @var string
     */
    protected $routeGroup = '';

    /**
     * Html of the form heading.
     *
     * @var string
     */
    protected $formHeadingHtml = '';

    /**
     * Html of the form body.
     *
     * @var string
     */
    protected $formBodyHtml = '';

    /**
     * Html of view to show.
     *
     * @var string
     */
    protected $formBodyHtmlForShowView = '';

    /**
     * User defined values
     *
     * @var string
     */
    protected $customData = '';

    /**
     * Template directory where views are generated
     *
     * @var string
     */
    protected $viewTemplateDir = '';/**
     * Template directory where views are generated
     *
     * @var string
     */
    protected $relatedModelsItems = '';

    /**
     * Delimiter used for replacing values
     *
     * @var array
     */
    protected $delimiter;
    /**
     * Delimiter used for replacing values
     *
     * @var array
     */
    protected $formBodyVueHtml;

    /**
     * Create a new command instance.
     */

    protected $propsHtml = "";

    public function __construct()
    {
        parent::__construct();

        if (config('crudstrap.view_columns_number')) {
            $this
                ->defaultColumnsToShow = config('crudstrap.view_columns_number');
        }

        $this->delimiter = config('crudstrap.custom_delimiter')
            ? config('crudstrap.custom_delimiter')
            : ['%%', '%%'];
    }

    public function reset(){
        $this->formFields = [];
        $this->inertia = false;
        $this->formFieldsHtml = '';
        $this->defaultColumnsToShow = 3;
        $this->varName = '';
        $this->crudName = '';
        $this->crudNameCap = '';
        $this->crudNameSingular = '';
        $this->primaryKey = 'id';
        $this->modelName = '';
        $this->modelNameCap = '';
        $this->viewName = '';
        $this->routePrefix = '';
        $this->routePrefixCap = '';
        $this->routeGroup = '';
        $this->formHeadingHtml = '';
        $this->formBodyHtml = '';
        $this->formBodyHtmlForShowView = '';
        $this->viewTemplateDir = '';
        $this->relatedModelsItems = '';
        $this->relatedModelsItems = '';
        $this->formBodyVueHtml="";
        $this->propsHtml = "";
    }

    /**
     * Execute the console command.
     *
     * @return void
     */

    public function handle()
    { 
        $this->reset();
        $formHelper = $this->option('form-helper');
        $stubs =  ($this->option('stub-path')) ? $this->option('stub-path') . '/' : "";
        $this->viewDirectoryPath = config('crudstrap.custom_template')
            ? config('crudstrap.path') . 'views/' . $stubs . $formHelper . '/'
            : __DIR__ . '/../stubs/views/' . $formHelper . '/';
        $this->crudName = strtolower($this->argument('name'));
        $this->varName = lcfirst($this->argument('name'));
        $this->inertia = $this->option('inertia');
        $this->viewPath = $this->option('view-path');
        $this->crudNameCap = ucwords($this->crudName);
        $this->crudNameSingular = Str::singular($this->crudName);
        $this->modelName = Str::singular($this->argument('name'));
        $this->modelNameCap = ucfirst($this->modelName);
        $this->customData = $this->option('custom-data');
        $this->primaryKey = $this->option('pk');
        $this->routeGroup = ($this->option('route-group'))
            ? $this->option('route-group') . '/'
            : $this->option('route-group');
        $this->routePrefix = ($this->option('route-group')) ? $this->option('route-group') : '';
        $this->routePrefixCap = ucfirst($this->routePrefix);
        $this->viewName = Str::of($this->argument('name'))->snake('-')->ucfirst();

        $viewDirectory = config('view.paths')[0] . '/';
        if ($this->option('view-path')) {
            $this->userViewPath = $this->option('view-path');
            $path = $viewDirectory . $this->userViewPath . '/' . $this->viewName . '/';
        } else {
            $path = $viewDirectory . $this->viewName . '/';
        }

        $this->viewTemplateDir = isset($this->userViewPath)
            ? $this->userViewPath . '.' . $this->viewName
            : $this->viewName;

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $fields = $this->option('fields');
        $fieldsArray = explode(';', $fields);
        $this->formFields = [];
        $tb = '    ';
        $validations = $this->option('validations');
        if ($fields) {
            $x = 0;
            foreach ($fieldsArray as $item) {

                $itemArray = explode('#', $item);
                if (Str::contains($itemArray[1], ':') || Str::contains($itemArray[1], '|')) {
                    $types = explode('|', str_replace(':', "|", $itemArray[1]));
                    $type = trim($types[0]);
                } else {
                    $type = trim($itemArray[1]);
                }
                $this->formFields[$x]['name'] = trim($itemArray[0]);
                $this->formFields[$x]['type'] = $type;
                $this->formFields[$x]['required'] = preg_match('/' . $itemArray[0] . '/', $validations) ? true : false;

                if (($type == 'select' || trim($type) == 'enum') && isset($itemArray[2])) {
                    $options = trim($itemArray[2]);
                    $options = str_replace('options=', '', $options);
                    $this->formFields[$x]['options'] = $options;
                }

                if ($type != 'select' && trim($type) != 'enum' &&  isset($itemArray[2]) && Str::startsWith($itemArray[2], 'options')) {
                    $options = trim($itemArray[2]);
                    $options = str_replace('options=', '', $options);
                    $this->formFields[$x]['options'] = $options;
                    $relmod = Str::of(str_replace("options=", "", $itemArray[2]))->ucfirst();
                    $this->propsHtml .= $tb . $tb . "{$relmod->lower()}s" . ':{ type: Object, required:true },' . PHP_EOL;
                    $this->relatedModelsItems .= ",'{$relmod->lower()}s'";
                }
                $x++;
            }
            
        }

        foreach ($this->formFields as $item) {
            $this->formFieldsHtml .= $this->createField($item);
        }

        $i = 0;
        foreach ($this->formFields as $key => $value) {
            /*if ($i == $this->defaultColumnsToShow) {
                break;
            }*/

            $field = $value['name'];
            $label = ucwords(str_replace('_', ' ', $field));
            $t = $this->inertia?'$t':'__';
            if ($this->option('localize')) {
                $label = '{{'.$t.'(\'' . $this->crudName . '.' . $field . '\') }}';
            }
            $this->formHeadingHtml .= $tb . $tb . $tb . $tb . $tb . $tb . $tb. $tb. $tb. '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . $label . '</th>' . PHP_EOL;;
            $this->formBodyVueHtml .=  $tb . $tb . $tb . $tb . $tb . $tb . $tb . $tb . $tb. '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" >{{ ' . $this->crudNameSingular . '.' . $field . '}}</td>' . PHP_EOL;;
            //$this->formBodyHtml .= $tb . $tb . $tb . $tb . $tb . '{data: \'' . $field . '\', name:  \'' . $field . '\', orderable: true},' . PHP_EOL;
            $this->formBodyHtmlForShowView .= '<tr><th> ' . $label . ' </th><td> {{ $%%crudNameSingular%%->' . $field . ' }} </td></tr>';
            $this->emptyVueForm .= $tb . $tb . $field . ': "",' . PHP_EOL;
            $this->filledVueForm .= $tb . $tb . $field . ':props.' . $this->crudNameSingular . '.' . $field . ',' . PHP_EOL;
            $i++;
        }

        //dd($this->emptyVueForm ,$this->filledVueForm );
        $this->templateStubs($path);

        $this->info('View created successfully.');
    }

 


    protected function createFormField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $required = $item['required'] ? 'required' : '';
        $markup = $this->getMarkup('form-field');
        $name = $item['name'];
        if (isset($item['options'])) { // for relationship selects
            $name = $item['options'];
        }
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $name, $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }
    protected function wrapField($item, $field)
    {
        $formGroup = $this->getMarkup('wrap-field');
        $labelText = ucwords(strtolower(str_replace('_', ' ', $item['name'])));
        if ($this->option('localize')) {
            $labelText = '__(\'' . $this->crudName . '.' . $item['name'] . '\')';
        }

        return sprintf($formGroup, $item['name'], $labelText, $field);
    }

    /**
     * Generate files from stub
     *
     * @param $path
     */
    protected function templateStubs($path)
    {
        $dynamicViewTemplate = config('crudstrap.dynamic_view_template')
            ? config('crudstrap.dynamic_view_template')
            : $this->defaultTemplating();
           
        foreach ($dynamicViewTemplate as $name) {
            $file = $this->viewDirectoryPath . $name . '.blade.stub';
            $newFile = $path . $name . '.blade.php';
            $vuePath = Str::of($this->viewPath)->ucfirst();
            $viewPath = $this->viewPath ==""? "js/Pages/" : "js/Pages/$vuePath/";
            if (!File::exists(resource_path($viewPath)))
            File::makeDirectory(resource_path($viewPath));
            $vuefile = $this->viewDirectoryPath. $name . '.vue.stub';
            $vuePageDir = $viewPath . ucfirst($this->crudName) . '/';
            $newVueFile = resource_path($vuePageDir .ucfirst($name).'.vue');
            
            if ($this->inertia && File::exists($vuefile) && File::isFile($vuefile)) {
                if(!File::isDirectory(resource_path($vuePageDir)))
                File::makeDirectory(resource_path($vuePageDir));
                if (!File::copy($vuefile, $newVueFile)) {
                    echo "failed to copy vuejs $file";
                } else {
                    $this->templateVars($newVueFile);
                    $this->userDefinedVars($newVueFile);
                }
                continue;
            }
            if (File::exists($file) && File::isFile($file)) {
                if (!File::isDirectory ($path))
                    File::makeDirectory($path);
                if (!File::copy($file, $newFile)) {
                    echo "failed to copy $file...\n";
                } else {
                    $this->templateVars($newFile);
                    $this->userDefinedVars($newFile);
                }
                continue;
            }
            $this->error("$name not found");
        }
    }



    /**
     * Default template configuration if not provided
     *
     * @return array
     */
    private function defaultTemplating()
    {
        return ['index', 'form', 'create', 'edit', 'show'];
    }




    /**
     * Update specified values between delimiter with real values
     *
     * @param $file
     * @param $vars
     */
    protected function templateVars($file)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        foreach ($this->vars as $var) {
            $replace = $start . $var . $end;
            File::put($file, str_replace($replace, $this->$var, File::get($file)));
        }
    }

    /**
     * Update custom values between delimiter with real values
     *
     * @param $file
     */
    protected function userDefinedVars($file)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        if ($this->customData !== null) {
            $customVars = explode(';', $this->customData);
            foreach ($customVars as $rawVar) {
                $arrayVar = explode('=', $rawVar);
                File::put($file, str_replace($start . $arrayVar[0] . $end, $arrayVar[1], File::get($file)));
            }
        }
    }

    

    /**
     * Form field generator.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createField($item)
    {
        switch ($this->typeLookup[$item['type']]) {
            case 'password':
                return $this->createPasswordField($item);
            case 'datetime-local':
            case 'time':
                return $this->createInputField($item);
            case 'radio':
                return $this->createRadioField($item);
            case 'textarea':
                return $this->createTextareaField($item);
            case 'select':
            case 'enum':
                return $this->createSelectField($item);
            default: // text
                return $this->createFormField($item);
        }
    }
    function getMarkup($path)
    {

        $vuepath = $this->viewDirectoryPath . "form-fields/{$path}.vue.stub";
        if (File::exists($vuepath) && File::isFile($vuepath))
            return File::get($vuepath);
        $path = $this->viewDirectoryPath . "form-fields/{$path}.blade.stub";
        if (File::exists($path) && File::isFile($path))
            return File::get($path);
    }



    /**
     * Create a password field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createPasswordField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $required = $item['required'] ? 'required' : '';
        $target = $this->getMarkup('password-field');
        $markup = str_replace($start . 'required' . $end, $required, $target);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a generic input field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createInputField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $required = $item['required'] ? 'required' : '';
        $target = $this->getMarkup('input-field');
        $markup = str_replace($start . 'required' . $end, $required, $target);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a yes/no radio button group using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createRadioField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $markup = $this->getMarkup('radio-field');
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);
        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a textarea field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createTextareaField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $required = $item['required'] ? 'required' : '';
        $markup = $this->getMarkup('textarea-field');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a select field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createSelectField($item)
    { 
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
        $required = $item['required'] ? 'required' : '';
        $data = json_decode($item['options'],true);
        $options = $this->json_encode_advanced($data);
        $markup = $this->getMarkup('select-field');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'options' . $end, $options, $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);
        return $this->wrapField(
            $item,
            $markup
        );
    }

    function json_encode_advanced(array $arr, $sequential_keys = false, $quotes = true, $beautiful_json = true)
    {

        $output = "{";
        $count = 0;
        foreach ($arr as $key => $value) {

            if ($this->isAssoc($arr) || (!$this->isAssoc($arr) && $sequential_keys == true)) {
                $output .=  $key . ' : ';
            }

            if (is_array($value)) {
                $output .= $this->json_encode_advanced($value, $sequential_keys, $quotes, $beautiful_json);
            } else if (is_bool($value)) {
                $output .= ($value ? 'true' : 'false');
            } else if (is_numeric($value)) {
                $output .= $value;
            } else {
                $output .= ($quotes || $beautiful_json ? "'" : '') . $value . ($quotes || $beautiful_json ? "'" : '');
            }

            if (++$count < count($arr)) {
                $output .= ', ';
            }
        }

        $output .= "}";

        return $output;
    }
    function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }



}
