<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\CrudConfig;
use Envatic\CrudStrap\Crud;
use Envatic\CrudStrap\CrudFile;
use Envatic\CrudStrap\Fields\Field;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudStrap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:strap {theme}';

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
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $theme = $this->argument('theme');
        $themeConfig = config('crudstrap.themes.' . trim($theme), null);
        if (!$themeConfig) return $this->error('Invalid Theme');
        $config = new CrudConfig(...$themeConfig);
        $files = File::allFiles($config->folder);
        uasort(
            $files,
            function ($a, $b) {
                return strnatcmp($a->getFilename(), $b->getFilename()); // or other function/code
            }
        );
        $secs = now()->diffInSeconds(\Carbon\Carbon::today());
        foreach ($files as $file) {
            $fileData = $file->openFile();
            $data = json_decode($fileData->fread($fileData->getSize()));
            $config = $config->override($data);
            if ($file->getExtension() != 'json') continue;
            $ext = explode('.', $file->getFilename());
            $nameStr = preg_replace('/[(0-9)]+_/', '', array_shift($ext));
            $name = Str::of($nameStr)->plural()->ucfirst();
            if (empty($name)) continue;
            $this->deleteCrudLang($name, $config);
            $crud = new CrudFile($data, $config, $name->singular());
            if ($config->has('migration')) {
                $datePrefix = date('Y_m_d_');
                $time = gmdate("His", $secs);
                $migrationPrefix =  $datePrefix . $time;
                if ($config->isPivot) $name = $name->singular();
                $this->call('crud:migration', array_filter([
                    'name' => $name,
                    'theme' => $theme,
                    'crud' => json_encode($data),
                    '--prefix' => $migrationPrefix,
                ]));
                $secs++;
            }
            if ($config->has('policy')) {
                $this->call('make:policy', array_filter([
                    'name' => $name->singular() . 'Policy',
                    '--model' => $name->singular(),
                ]));
            }
            if ($config->has('resource')) {
                $this->call('crud:resource', array_filter([
                    'name' => $name->singular(),
                    'theme' => $theme,
                    'crud' => json_encode($data),
                    '--force' => $config->force,
                ]));
            }

            if ($config->has('enums')) {
                $enums = $crud->fields->filter(fn (Field $f) => $f->type()->isEnum());
                foreach ($enums as $enum) {
                    $this->call('crud:enum', array_filter([
                        'name' =>  $enum->getEnumClass(),
                        '--cases' => $enum->options()->getEnumValues(),
                        '--int' => $enum->options()->isIntEnum(),
                        '--force' => $config->force
                    ]));
                }
            }

            if ($config->has('controller')) {
                $this->call('crud:controller', array_filter([
                    'name' => $name,
                    'theme' => $theme,
                    'crud' => json_encode($data)
                ]));
            }


            if ($config->has('model')) {
                $this->call('crud:model', array_filter([
                    'name' => $name->singular(),
                    'theme' => $theme,
                    'crud' => json_encode($data),
                    '--force' => $config->force,
                ]));
            }


            if ($config->has('routes')) {
                $isAdded = Crud::addResourceRoutes($name, $config);
                if ($isAdded) {
                    $this->info($name . ' Resource route added to routes/web.php');
                } else {
                    $this->info('Unable to add the routes for ' . $name);
                }
            }


            if ($config->has('view')) {
                $this->call('crud:view', array_filter([
                    'name' => $name,
                    'theme' => $theme,
                    'crud' => json_encode($data)
                ]));
            }
        }
        $this->callSilent('optimize');
    }


    function deleteCrudLang(string $name,  CrudConfig $config)
    {
        if (!$config->has('lang')) return;
        if (empty($config->locales)) return;
        foreach ($config->locales as $locale) {
            $path = lang_path($locale . '/' . lcfirst($name) . '.php');
            if (File::exists($path)) {
                if ($config->force) {
                    File::delete($path);
                    $this->info("$name lang file deleted succesfully");
                } else {
                    $this->error("$name already exists");
                }
            }
        }
    }
}
