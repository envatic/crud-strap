<?php

namespace Envatic\CrudStrap\Commands;

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
        $theme_name = $this->argument('theme');
        $cruds = explode(',', $this->option('crud'));
        $themeData = collect(config('crudstrap.themes'))
            ->first(fn ($i) => $i['name'] == $theme_name);
        if (empty($themeData))
            return $this->error('Invalid Theme');
        $default = [
            'view-path' => $theme_name,
            'stub-path' => $theme_name,
            "name" => 'default',
            'folder' => "crud/",
            'form-helper' => 'html',
            'model-namespace' => 'Models',
            'soft-deletes' => true,
            'controller-namespace' => '',
            'pk' => 'id',
            'pagination' => '25',
            'route-group' => $theme_name,
            'force' => false,
            'locales' => 'en',
            'only' => 'all',
        ];
        $theme = collect($default)
            ->merge(collect($themeData))->all();
       
        $files = File::allFiles(base_path($theme['folder']));
        $validfiles = collect($files)->filter(function ($file) use ($cruds) {
            $ext = explode('.', $file->getFilename());
            $nameStr = Str::of(preg_replace('/[(0-9)]+_/', '', array_shift($ext)));
            return  in_array($nameStr->lower()->singular(), array_map('strtolower', $cruds))
                || in_array($nameStr->lower()->plural(), array_map('strtolower', $cruds));
        })->map(function ($file) {
            $data = json_decode(File::get($file->getPathName()));
            $ext = explode('.', $file->getFilename());
            $nameStr = preg_replace('/[(0-9)]+_/', '', array_shift($ext));
            $data->crudName =  $nameStr;
            return $data;
        });
        foreach ($validfiles as $file) {
            $config = $file->config ?? [];
            $theme = collect($theme)
                ->merge(collect($config))
                ->all();
            $all = Str::of($theme['only'])->explode(',');
            $crud = Str::of($file->crudName);
            $model = app_path('Models/' . $crud->singular()->ucfirst() . '.php');
            $resource = app_path('Http/Resources/' . $crud->ucfirst()->singular() . '.php');
            $policy = app_path('Policies/' . $crud->ucfirst()->singular() . 'Policy.php');
            $migration = 'create_' . $crud->lower() . '_table';

            $locales = Str::of($theme['locales'])->explode(",");
            if ($all->contains('lang') || $all->contains('all')) {
                if ($locales->count()) {
                    $locales->map(function ($locale) use ($crud) {
                        $path = lang_path($locale);
                        $langFile = $path .'/'. $crud->lower(). '.php';
                        if (File::isFile($langFile)) {
                            File::delete($langFile);
                            $this->info("lang {$locale} deleted succesfully");
                        } else {
                            $this->error("Locales {$locale} Found");
                        }
                    });
                } else {
                    $this->error('Locales Found');
                }
            }

            if ($all->contains('enums') || $all->contains('all')) {
                collect($file->fields)->each(function ($field) use ($crud) {
                    $type = Str::of($field->type)
                        ->replace(':', '|')
                        ->explode('|')
                        ->shift(1);
                    if (in_array($type, ['select', 'enum'])) {
                        $file = app_path("Enums/{$crud->singular()->ucfirst()}{$field->name}.php");
                        File::delete($file);
                        $this->info("Enum {$crud->singular()->ucfirst()}{$field->name} deleted succesfully");
                    }
                });
            }

            if ($all->contains('view') || $all->contains('all')) {
                $folder = $theme['view-path'] == "" ? "" : $theme['view-path'] . '/';
                $path = resource_path("views/{$folder}{$crud->lower()}");
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                    $this->info('Blade Views deleted succesfully');
                } else {
                    $this->error('No Blade Views');
                }
                $inertiaPath = resource_path("js/Pages/{$folder}{$crud->ucfirst()}");
                if (File::isDirectory($inertiaPath)) {
                    File::deleteDirectory($inertiaPath);
                    $this->info('Inertia Views deleted succesfully');
                } else {
                    $this->error('No Inertia Views');
                }
            }

            if ($all->contains('factory') || $all->contains('all')) {
                $file = database_path("factories/{$crud->singular()->ucfirst()}Factory.php");
                if (File::isFile($file)) {
                    File::delete($file);
                    $this->info('factory deleted succesfully');
                } else {
                    $this->error('no factory ');
                }
            }

            if ($all->contains('transformer') || $all->contains('all')) {
                $file = app_path("Transformers/{$crud->singular()->ucfirst()}Transformer,php");
                if (File::isFile($file)) {
                    File::delete($file);
                    $this->info('transformer deleted succesfully');
                } else {
                    $this->error('no transformer');
                }
            }
            if ($all->contains('route') || $all->contains('all')) {
                $routes = collect([
                    base_path('routes/web.php'),
                    base_path('routes/api.php')
                ]);
                $routes->each(function ($route) use ($crud) {
                    if (File::isFile($route)) {
                        $file = File::get($route);
                        if (preg_match('/(\#' . $crud->lower() . ')/', $file, $matches) == 1) {
                            $outfile = preg_replace('/(\#' . $crud->lower() . ')(.*?)(\#' . $crud->lower() . ')/s', "", $file);
                            File::replace($route,   $outfile);
                            $this->info('Route info cleared');
                        } else {
                            $this->error("$route has no {$crud->lower()} routes");
                        }
                    }
                });
            }
            if ($all->contains('controller') || $all->contains('all')) {
                $controllerNamespace =  $theme['controller-namespace'] == "" ? "" : $theme['controller-namespace'] . '/';
                $controller = app_path("Http/Controllers/{$controllerNamespace}{$crud->ucfirst()}Controller.php");
                if (File::isFile($controller)) {
                    File::delete($controller);
                    $this->info('controller deleted succesfully');
                } else {
                    $this->error('no controller ');
                }
            }
            if ($all->contains('resource') || $all->contains('all')) {
                if (File::isFile($resource)) {
                    File::delete($resource);
                    $this->info('resource deleted succesfully');
                } else {
                    $this->error('no resource ');
                }
            }
            if ($all->contains('model') || $all->contains('all')) {
                if (File::isFile($model)) {
                    File::delete($model);
                    $this->info('model deleted succesfully');
                } else {
                    $this->error('no model ');
                }
            }
            if ($all->contains('policy') || $all->contains('all')) {
                if (File::isFile($policy)) {
                    File::delete($policy);
                    $this->info('policy deleted succesfully');
                } else {
                    $this->error('no policy ');
                }
            }
            if ($all->contains('migration') || $all->contains('all')) {
                $filesNames = File::files(base_path() . '/database/migrations/');
                $mdelete = false;
                foreach ($filesNames as $file) {
                    if (Str::contains($file->getFilename(),  $migration)) {
                        File::delete($file->getPathname());
                        $this->info('migration deleted succesfully');
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
}
