## Configuration

You will find a configuration file located at `config/crudstrap.php`

### Custom Template

When you want to use your own custom template files, then you should turn it on and it will use the files from `resources/crud-strap/`

```php
'custom_template' => false,
```

### Path

You can change your template path easily, the default path is `resources/crud-strap/`.

```php
'path' => base_path('resources/crud-strap/'),
```



### Custom Delimiter

Set your delimiter which you use for your template vars. The default delimiter is `%%` in everywhere.

```php
'custom_delimiter' => ['%%', '%%'],
```
Note: You should use the delimiter same as yours template files.

### View Template Vars

This configuration will help you to use any custom template vars in the views 

```php
'dynamic_view_template' => [],
```

### Themes

This is use to strap parts the app in one command
Example : you can have two configs one for frontend and  other backend;
and run
`php artisan crud:strap frontend`
`php artisan crud:strap backend`

Provide [config](configuration.md) options for  generaing your apps crud
The defaults are wriiten below
lets see axample below;
```php
'themes' => [
    [
        //strapp
        "name" => 'backend', // any name of part of your app
        'folder' => "crud/backend", // use all json in 'app/crud/backend'folder
        /// config 
        'form-helper' => 'html'|| 'laravelcollective',
        'model-namespace' => 'Models' || null// leave empty if models are in app folder,
        'soft-deletes' => true,
        'controller-namespace' => 'Admin', // App/Http/Controllers/Admin
        'pk' => 'id', // preivte keys
        'pagination' => '25', // only for blade templates
        'route-group' => 'admin',  // all routes will put under group admin
        'force' => false, // delete old files if found
        'inertia' => false, // generate inertia vues
        'locales' => 'en',  // =languages to be generated
         //policy,transformer,controller,model,migration,view,route,factory,resource,lang, enums
        'only' => 'all', // generate all crude files above
       

    ],[
        //strapp
        "name" => 'admin',
        'folder' => "crud/",
        'form-helper' => 'html',
    ]

],
```
Defualts
```php
        'folder' => "crud/",
        'form-helper' => 'html',
        'model-namespace' => 'Models',
        'soft-deletes' => true,
        'controller-namespace' => '',
        'pk' => 'id',
        'pagination' => '25',
        'route-group' => '', 
        'force' => false,
        'inertia' => false,
        'locales' => 'en',  
        'only' => 'all',
```
### Explanation
After adding

[&larr; Back to index](README.md)
