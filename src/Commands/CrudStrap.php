<?php

namespace Envatic\CrudStrap\Commands;

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
        $theme_name = $this->argument('theme');
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
            ->merge(collect($themeData))
            ->mapWithKeys(fn ($v, $k) => ["--$k" => $v])->all();
        $files = File::allFiles(base_path($theme['--folder']));
        $folder = ltrim(((string)$theme['--folder']), "\\");
        $only =  $theme['--only'];
        unset($theme['--folder']);
        unset($theme['--name']);
        uasort(
            $files,
            function ($a, $b) {
                return strnatcmp($a->getFilename(), $b->getFilename()); // or other function/code
            }
        );
        $secs = now()->diffInSeconds(\Carbon\Carbon::today());
        foreach ($files as $file) {
            $fileData = $file->openFile();
            $data = json_decode($fileData->fread($fileData->getSize()), true);
            $config = $data['config'] ?? [];
            $themex = $theme;
            if ($file->getExtension() != 'json') continue;
            $ext = explode('.', $file->getFilename());
            $nameStr = preg_replace('/[(0-9)]+_/', '', array_shift($ext));
            if (in_array($nameStr, config('crudstrap.pivot_tables'))) {
                $themex['--only'] = 'migration';
            }
            //overide with json config
            $themex = collect($themex)
                ->merge(collect($config)->mapWithKeys(fn ($v, $k) => ["--$k" => $v]))
                ->all();
            $name = Str::of($nameStr)->plural()->ucfirst();
            if (empty($name)) continue;
            $themex['--fields_from_file'] = $folder . "/" . $file->getFilename();
            $themex['name'] = $name;
            if ($themex['--force'])
                $this->deleteCrudLang($name, $themex);
            // inorder to ensure the order of the Migrations 
            // we concoct the time;
            $datePrefix = date('Y_m_d_');
            $time = gmdate("His", $secs);
            $themex['--prefix'] = $datePrefix . $time;
            $this->warn('Creating crud.........');
            $this->call('crud:gen', $themex);
            $this->info($name . '. ===> DONE');
            $secs++;
        }
    }


    function deleteCrudLang($name, $theme)
    {
        $only =  Str::of($theme['--only']);
        if (!$only->contains(['all', 'lang'])) return;
        $activeLocales = $theme['--locales'] ?? null;
        if (!$activeLocales) return;
        $locales = explode(',', $activeLocales);
        foreach ($locales as $locale) {
            $path = config('view.paths')[0] . '/../lang/' . $locale . '/';
            $path . lcfirst($name) . '.php';
            if (File::exists($path)) {
                File::delete($path);
                $this->info('model deleted succesfully');
            }
        }
    }
}
