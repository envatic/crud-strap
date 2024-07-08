<?php

namespace Envatic\CrudStrap;

use Illuminate\Support\Str;

class CrudConfig
{

    function __construct(
        public $viewPath = null,
        public $stubPath = null,
        public $name = 'default',
        public $folder = 'crud/',
        public $formHelper = 'inertia',
        public $modelNamespace = '',
        public $softdeletes = true,
        public $controllerNamespace = '',
        public $primaryKey = 'id',
        public $pagination = 25,
        public $routeGroup = null,
        public $force = false,
        public $inertia = true,
        public $isPivot = false,
        public array $locales = ['en'],
        public array $only = ['all'],
    ) {
    }

    public function override(object $crud): static
    {
        if (!isset($crud?->config)) return $this;
        $config = $crud->config;
        $this->viewPath = $config->viewPath ?? $this->viewPath;
        $this->name = $config->name ?? $this->name;
        $this->folder = $config->folder ?? $this->folder;
        $this->formHelper = $config->formHelper ?? $this->formHelper;
        $this->modelNamespace  = $config->modelNamespace ?? $this->modelNamespace;
        $this->softdeletes = $config->softdeletes ?? $this->softdeletes;
        $this->controllerNamespace = $config->controllerNamespace ?? $this->controllerNamespace;
        $this->primaryKey = $config->primaryKey ?? 'id';
        $this->pagination = $config->pagination ?? $this->pagination;
        $this->routeGroup = $config->routeGroup ?? $this->routeGroup;
        $this->force = $config->force ?? $this->force;
        $this->locales = $config->locales ?? $this->locales;
        $this->inertia = $config->inertia ?? $this->inertia;
        $this->isPivot = $config->isPivot ?? false;
        $this->only  = $config->only ?? $this->only;
        return $this;
    }

    public function has($crud): bool
    {
        if (in_array('all', $this->only)) return true;
        return in_array($crud, $this->only);
    }

    public function isApi(): bool
    {
        return Str::of($this->controllerNamespace)
            ->lower()
            ->startsWith('api\\');
    }
}
