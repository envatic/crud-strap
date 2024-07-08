<?php

namespace Envatic\CrudStrap;

use Envatic\CrudStrap\Commands\CrudControllerCommand;
use Envatic\CrudStrap\Commands\CrudEnumCommand;
use Envatic\CrudStrap\Commands\CrudMigrationCommand;
use Envatic\CrudStrap\Commands\CrudModelCommand;
use Envatic\CrudStrap\Commands\CrudPolicyCommand;
use Envatic\CrudStrap\Commands\CrudResourceCommand;
use Envatic\CrudStrap\Commands\CrudStrap;
use Envatic\CrudStrap\Commands\CrudViewCommand;
use Envatic\CrudStrap\Commands\CrudDelete;
use Illuminate\Support\ServiceProvider;


class CrudStrapServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath($raw = __DIR__ . '/../config/crudstrap.php') ?: $raw;
        $this->publishes([
            $source => $this->app->configPath('crudstrap.php'),
            __DIR__ . '/../publish/Components/' => base_path('resources/js/Components'),
            //__DIR__ . '/../publish/Layouts/' => base_path('resources/js/Layouts'),
            __DIR__ . '/stubs/' => base_path('resources/crud-strap/'),
            __DIR__ . '/crud/' => base_path('crud'),
            __DIR__ . '/Traits/HasUuid.php.stub' => app_path('Traits/HasUuid.php'),
            __DIR__ . '/Traits/WhenMorphed.php.stub' => app_path('Traits/WhenMorphed.php'),
        ], 'crudstrap');
        $this->mergeConfigFrom($source, 'crudstrap');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            CrudControllerCommand::class,
            CrudEnumCommand::class,
            CrudMigrationCommand::class,
            CrudModelCommand::class,
            CrudPolicyCommand::class,
            CrudResourceCommand::class,
            CrudStrap::class,
            CrudViewCommand::class,
            CrudDelete::class,
        );
    }
}
