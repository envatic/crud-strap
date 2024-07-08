<?php

namespace Envatic\CrudStrap\Fields;

use Illuminate\Support\Str;

class FieldOptions
{

    function __construct(public $options)
    {
    }

    public function getDatabaseEnums(): string
    {
        return collect($this->options)
            ->keys()
            ->map(fn ($k) => "\"$k\"")
            ->implode(",");
    }

    public function getEnumValues()
    {
        $cases = "";
        foreach ($this->options as $key => $field) {
            if (is_int($key)) {
                $cases .= "\tcase {$field} = {$key};\n";
            } else {
                $name = Str::of($key)->replace(['-', '_', ':'], "_")->upper();
                $value = $key;
                $cases .= "\tcase {$name} = '{$value}';\n";
            }
        }
        return $cases;
    }

    public function isIntEnum()
    {
        return is_array($this->options);
    }


    public function getRadioCardOptions()
    {
        return collect($this->options)
            ->map(function (string|object $option, string $name) {
                if (is_string($option)) return "{key:'$name',value:'$name', title:'$option',subtitle:null}";
                return "{key:'$name',value:'$name', title:'$option->title',subtitle:'$option->subtitle'}";
            });
    }

    public function getRadioSelectOptions()
    {
        return collect($this->options)
            ->map(function (string $option, string $name) {
                return "{key:'$name', value:'$name',label:'$option'}";
            });
    }


    public function getValidation(): string
    {
        return "in:" . implode(",", array_keys($this->options));
    }
}
