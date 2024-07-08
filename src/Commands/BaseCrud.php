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
    protected ?CrudConfig $config;

    /**
     * The config for this crud
     *
     * @var CrudFile
     */
    protected ?CrudFile $crud = null;


    public function handle()
    {
        $this->config = null;
        $this->crud = null;
        $this->init();
        parent::handle();
    }

    public function init()
    {
        $crudJson = json_decode(trim($this->argument('crud')));
        $theme = $this->argument('theme');
        $themeConfig = config('crudstrap.themes.' . trim($theme), null);
        if (!$themeConfig) return $this->error('Invalid Theme');
        $config = new CrudConfig(...$themeConfig);
        $this->config = $config->override($crudJson);
        $this->crud = new CrudFile($crudJson, $this->config, $this->argument('name'));
    }
}
