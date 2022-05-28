<?php

namespace Envatic\CrudStrap\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:delete {cruds}';

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
        $crudsList = $this->argument('cruds');
        collect(explode(',', $crudsList))->each(function($crud){
            $this->remove(trim($crud));
        });
    }

    protected function remove (String $crud)
    {   
        $model = app_path('Models/' . ucfirst(Str::singular($crud)) . '.php');
        $resource = app_path('Http/Resources/' . ucfirst(Str::singular($crud)) . '.php');
        $controller = app_path('Http/Controllers/Admin/' . ucfirst($crud) . 'Controller.php');
        $policy = app_path('Policies/' . ucfirst(Str::singular($crud)) . 'Policy.php');
        $migration = 'create_' . strtolower($crud) . '_table';
        if (file_exists($policy)) {
            unlink($policy);
            $this->info('policy deleted succesfully');
        } else {
            $this->info('no policy ');
        }
        if (file_exists($controller)) {
            unlink($controller);
            $this->info('controller deleted succesfully');
        } else {
            $this->info('no controller ');
        }
        if (file_exists($resource)) {
            unlink($resource);
            $this->info('resource deleted succesfully');
        } else {
            $this->info('no resource ');
        }
        if (file_exists($model)) {
            unlink($model);
            $this->info('model deleted succesfully');
        } else {
            $this->info('no model ');
        }
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
            $this->info('no migration');
        }
    }
}
