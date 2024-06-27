<?php

namespace Envatic\CrudStrap\Commands;

use Envatic\CrudStrap\CrudConfig;
use Envatic\CrudStrap\CrudFile;
use Illuminate\Console\GeneratorCommand;

abstract class BaseCrud  extends GeneratorCommand
{
    /**
     * The config for this crud
     *
     * @var CrudConfig
     */
    protected CrudConfig $config;

    /**
     * The config for this crud
     *
     * @var CrudFile
     */
    protected CrudFile $crud;


    public function handle()
    {
        $crudJson = json_decode(trim($this->argument('crud')));
        $config = new CrudConfig(...(config('crudstrap.themes.' . trim($this->argument('theme')))));
        $this->config = $config->override($crudJson);
        $this->crud = new CrudFile($crudJson, $this->config, $this->getNameInput());
        parent::handle();
    }
}
