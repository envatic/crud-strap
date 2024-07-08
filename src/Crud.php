<?php

namespace Envatic\CrudStrap;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str;

class Crud
{


    public static function addResourceRoutes(Stringable $name, CrudConfig $config)
    {
        $routeFile = base_path('routes/web.php');
        $controller =  $name->ucfirst()->plural() . 'Controller';
        $routeContent = static::getRoutes($name, $controller, $config->routeGroup);
        $routes = "\n" . implode("\n", $routeContent);
        if (file_exists($routeFile)) {
            $controller =  $name->ucfirst()->plural() . 'Controller';
            $routeContent = static::getRoutes($name, $controller, $config->routeGroup);
            $routes = "\n" . implode("\n", $routeContent);
            $file = File::get($routeFile);
            $routeName = $name->lower()->plural()->snake();
            if (preg_match('/(\#' . $routeName . ')/', $file, $matches) == 1) {
                $outfile = preg_replace('/(\#' . $routeName . ')(.*?)(\#' . $routeName . ')/s', $routes, $file);
                File::replace($routeFile,   $outfile);
                return true;
            }
            File::append($routeFile, $routes);
            return true;
        }
        return false;
    }

    protected static function getRoutes(Stringable $name, string $controller, ?string $prefix)
    {
        $routeName = $name->lower()->plural()->snake();
        $routeVar = $routeName->singular();
        $prefix = $prefix ? "{$prefix}." : "";
        return [
            "#$routeName
 Route::name('{$prefix}{$routeName}')->controller({$controller}::class)->group(function () {
    Route::get('/{$routeName}', 'index')->name('index');
    Route::get('/{$routeName}/create', 'create')->name('create');
    Route::post('/{$routeName}/store', 'store')->name('store');
    Route::get('/{$routeName}/{{$routeVar}}/show', 'show')->name('show');
    Route::get('/{$routeName}/{{$routeVar}}/edit', 'edit')->name('edit');
    Route::put('/{$routeName}/{{$routeVar}}', 'update')->name('update');
    Route::put('/{$routeName}/toggle/{{$routeVar}}', 'toggle')->name('toggle');
    Route::delete('/{$routeName}/{{$routeVar}}', 'destroy')->name('destroy');
});
#$routeName"
        ];
    }

    /**
     * Convert the given values to boolean if they are string "true" / "false".
     *
     * @param  array  $values
     * @return array
     */
    public static function convertValuesToBoolean($values)
    {
        return array_map(function ($value) {
            if ($value === 'true') {
                return true;
            } elseif ($value === 'false') {
                return false;
            }
            return $value;
        }, $values);
    }

    /**
     * Convert the given values to numeric if not in single quottes
     *
     * @param  array  $values
     * @return array
     */
    public static function convertValuesToNumbers($values)
    {
        //preserve strings
        return array_map(fn ($v) => static::shouldSkipTypes($v)
            ? $v
            : (is_numeric($v) ? $v * 1 : $v), $values);
    }

    /**
     * Convert the given values to null if they are string "null".
     *
     * @param  array  $values
     * @return array
     */
    public static function convertValuesToNull($values)
    {
        return array_map(function ($value) {
            return Str::lower($value) === 'null' ? null : $value;
        }, $values);
    }

    public static function shouldSkipTypes($item)
    {
        return preg_match_all('~(?|"([^"]*)"|\'([^\']*)\')~', $item) || Str::of($item)->contains("::class");
    }

    public static function parseFunctionParams($paramList)
    {
        $params =  str_getcsv($paramList);
        $boolean = static::convertValuesToBoolean($params);
        $null = static::convertValuesToNull($boolean);
        $clean = static::convertValuesToNumbers($null);
        return collect($clean)->map(function ($item) {
            if (is_bool($item)) return json_encode($item);
            if (static::shouldSkipTypes($item))
                return $item;
            if (is_string($item)) return "'$item'";
            return $item;
        })->implode(',');
    }

    public static  function stringifyArray($array): string
    {
        return collect($array)->map(function ($item) {
            // skip items in qoutes
            if (preg_match_all('~(?|"([^"]*)"|\'([^\']*)\')~', $item))
                return $item;
            //skip classes
            if (Str::of($item)->contains("::class")) return $item;
            return "'$item'";
        })->implode(',');
    }


    public function npm()
    {
        return [
            "v-calendar@next",
            "@vueuse/components",
            "@vueuse/core",
            "@heroicons/vue",
            "@popperjs/core",
            "vue3-emoji-picker",
            "@headlessui/vue",
            "oh-vue-icons",
            "vue-filepond",
            "filepond-plugin-file-validate-type",
            "filepond-plugin-image-preview"
        ];
    }
}
