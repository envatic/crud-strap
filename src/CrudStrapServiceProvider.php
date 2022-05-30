<?php

namespace Envatic\CrudStrap;

use Envatic\CrudStrap\Commands\CrudApiCommand;
use Envatic\CrudStrap\Commands\CrudApiControllerCommand;
use Envatic\CrudStrap\Commands\CrudCommand;
use Envatic\CrudStrap\Commands\CrudControllerCommand;
use Envatic\CrudStrap\Commands\CrudDbFactoryCommand;
use Envatic\CrudStrap\Commands\CrudEnumCommand;
use Envatic\CrudStrap\Commands\CrudLangCommand;
use Envatic\CrudStrap\Commands\CrudMigrationCommand;
use Envatic\CrudStrap\Commands\CrudModelCommand;
use Envatic\CrudStrap\Commands\CrudObserverCommand;
use Envatic\CrudStrap\Commands\CrudPolicyCommand;
use Envatic\CrudStrap\Commands\CrudResourceCommand;
use Envatic\CrudStrap\Commands\CrudStrap;
use Envatic\CrudStrap\Commands\CrudTransformerCommand;
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
        $this->publishes([
            __DIR__ . '/../config/crudstrap.php' => config_path('crudstrap.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/views/' => base_path('resources/views/'),
        ]);

        $this->publishes([
            __DIR__ . '/stubs/' => base_path('resources/crud-strap/'),
        ]);
        $this->publishes([
            __DIR__ . '/crud/' => base_path('crud'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            CrudApiCommand::class,
            CrudApiControllerCommand::class,
            CrudCommand::class,
            CrudControllerCommand::class,
            CrudDbFactoryCommand::class,
            CrudEnumCommand::class,
            CrudLangCommand::class,
            CrudMigrationCommand::class,
            CrudModelCommand::class,
            CrudObserverCommand::class,
            CrudPolicyCommand::class,
            CrudResourceCommand::class,
            CrudStrap::class,
            CrudTransformerCommand::class,
            CrudViewCommand::class,
            CrudDelete::class,
        );
    }
}
