<?php

namespace Envatic\CrudStrap;

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
            'Envatic\CrudStrap\Commands\CrudCommand',
            'Envatic\CrudStrap\Commands\CrudControllerCommand',
            'Envatic\CrudStrap\Commands\CrudModelCommand',
            'Envatic\CrudStrap\Commands\CrudMigrationCommand',
            'Envatic\CrudStrap\Commands\CrudViewCommand',
            'Envatic\CrudStrap\Commands\CrudLangCommand',
            'Envatic\CrudStrap\Commands\CrudApiCommand',
            'Envatic\CrudStrap\Commands\CrudApiControllerCommand'
        );
    }
}
