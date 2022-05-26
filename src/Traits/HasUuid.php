<?php

namespace Envatic\CrudStrap\Traits;

use Illuminate\Support\Str;

trait HasUuid
{

    /**
     * Boot function from laravel.
     */
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
