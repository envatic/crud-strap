<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\CrudConfig;
use Envatic\CrudStrap\CrudFile;
use Envatic\CrudStrap\Fields\Field;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudDelete extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:delete  {theme : The name of the theme.}
                            {--crud= : Field names cruds.}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap theme';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function handle()
    {
        $themeConfig = config('crudstrap.themes.' . trim($this->argument('theme')), null);
        if (!$themeConfig) return $this->error('Invalid Theme');
        $config = new CrudConfig(...$themeConfig);
        $listCruds = $this->option('crud');
        $cruds = explode(',', $this->option('crud'));
        $files = File::allFiles($config->folder);
        $valid  =  $listCruds == "" ? collect($files) : collect($files)->filter(function ($file) use ($cruds) {
            $ext = explode('.', $file->getFilename());
            $nameStr = Str::of(preg_replace('/[(0-9)]+_/', '', array_shift($ext)));
            return  in_array($nameStr->lower()->singular(), array_map('strtolower', $cruds))
                || in_array($nameStr->lower()->plural(), array_map('strtolower', $cruds));
        });

        $validfiles =  $valid->map(function ($file) {
            $data = json_decode(File::get($file->getPathName()));
            if ($this->jsonLastError()) {
                $this->error("Error in file: {$file->getPathName()}");
                $this->error($this->jsonLastError());
            }
            if (!$data) dd($file->getPathName());
            $ext = explode('.', $file->getFileName());
            $nameStr = preg_replace('/[(0-9)]+_/', '', array_shift($ext));
            $data->crudName =  $nameStr;
            $data->fileName =  $file->getFileName();
            return $data;
        });
        foreach ($validfiles as $file) {
            $config = $config->override($file);
            $crud = new CrudFile($file, $config, $file->crudName);

            // Delete Enums
            if ($config->has('enums')) {
                $crud->fields->each(function (Field $field) use ($crud) {
                    if ($field->type()->isEnum()) {
                        $fieldName = Str::of($field->name())->ucfirst();
                        $file = app_path("Enums/{$crud->modelName()}{$fieldName}.php");
                        File::delete($file);
                        $this->info("Enum {$crud->modelName()}{$fieldName} deleted succesfully");
                    }
                });
            }

            // Delete Views
            if ($config->has('view')) {
                $folder = $config->viewPath == "" ? "" :  $config->viewPath . '/';
                $inertiaPath = resource_path("js/Pages/{$folder}{$crud->crudName()->ucfirst()}");
                if (File::isDirectory($inertiaPath)) {
                    File::deleteDirectory($inertiaPath);
                    $this->info('Inertia Views deleted succesfully');
                } else {
                    $this->error('No Inertia Views Found for ' . $crud->crudName()->ucfirst());
                }
            }

            // Remove Route from route file
            if ($config->has('route')) {
                $route = base_path('routes/web.php');
                if (File::isFile($route)) {
                    $file = File::get($route);
                    if (preg_match('/(\#' . $crud->crudName() . ')/', $file, $matches) == 1) {
                        $outfile = preg_replace('/(\#' . $crud->crudName() . ')(.*?)(\#' . $crud->crudName() . ')/s', "", $file);
                        $outfile = str_replace('_types', "", $outfile);
                        File::replace($route,   $outfile);
                        $this->info('Route info cleared');
                    } else {
                        $this->error("$route has no {$crud->crudName()} routes");
                    }
                }
            }

            // Delete Controller
            if ($config->has('controller')) {
                $controllerNamespace =  $config->controllerNamespace  == "" ? "" : $config->controllerNamespace . '/';
                $controller = app_path("Http/Controllers/{$controllerNamespace}{$crud->crudName()->ucfirst()}Controller.php");
                if (File::isFile($controller)) {
                    File::delete($controller);
                    $this->info('controller deleted succesfully');
                } else {
                    $this->error('no controller ');
                }
            }

            // Delete Resource
            if ($config->has('resource')) {
                $resource = app_path('Http/Resources/' . $crud->modelName() . '.php');
                if (File::isFile($resource)) {
                    File::delete($resource);
                    $this->info('resource deleted succesfully');
                } else {
                    $this->error('no resource ');
                }
            }

            // Delete Model
            if ($config->has('model')) {
                $model = app_path('Models/' . $crud->modelName() . '.php');
                if (File::isFile($model)) {
                    File::delete($model);
                    $this->info('model deleted succesfully');
                } else {
                    $this->error('no model ');
                }
            }

            // Delete Policy
            if ($config->has('policy')) {
                $policy = app_path('Policies/' . $crud->modelName() . 'Policy.php');
                if (File::isFile($policy)) {
                    File::delete($policy);
                    $this->info('policy deleted succesfully');
                } else {
                    $this->error('no policy ');
                }
            }

            if ($config->has('migration')) {
                $migration = 'create_' . $crud->crudName() . '_table';
                $filesNames = File::files(base_path() . '/database/migrations/');
                $mdelete = false;
                foreach ($filesNames as $file) {
                    if (Str::contains($file->getFilename(),  $migration)) {
                        File::delete($file->getPathname());
                        $this->info($file->getFilename() . ' migration deleted succesfully');
                        $mdelete = true;
                    }
                }
                if (!$mdelete) {
                    $this->error('no migration');
                }
            }
            // transformer,,,
        }
    }
    public function jsonLastError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return false;
                break;
            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return ' - Unknown error';
                break;
        }
    }
}
