<?php

return [

    'custom_template' => true,

    /*
    |--------------------------------------------------------------------------
    | Crud Generator Template Stubs Storage Path
    |--------------------------------------------------------------------------
    |
    | Here you can specify your custom template path for the generator.
    |
     */

    'path' => base_path('resources/crud/'),
    /*
    |--------------------------------------------------------------------------
    | If Using Jetstream Teams, generates  Compatibel User Model
    |--------------------------------------------------------------------------
    |
    | Here you can create A Teams Compartible Model.
    |
     */

    'user_teams_model' => true,

    /**
     * Columns number to show in view's table.
     */
    'view_columns_number' => 3,

    /**
     * Delimiter for template vars
     */
    'custom_delimiter' => ['%%', '%%'],

    /*
    |--------------------------------------------------------------------------
    | Dynamic templating
    |--------------------------------------------------------------------------
    |
    | Here you can specify your customs templates for the generator.
    | You can set new templates or delete some templates if you do not want them.
    | You can also choose which values are passed to the views and you can specify a custom delimiter for all templates
    |
    | Those values are available :
    |
    | formFields
    | formFieldsHtml
    | varName
    | crudName
    | crudNameCap
    | crudNameSingular
    | primaryKey
    | modelName
    | modelNameCap
    | viewName
    | routePrefix
    | routePrefixCap
    | routeGroup
    | formHeadingHtml
    | formBodyHtml
    |
    |
     */
    'dynamic_view_template' => [
        'index' => ['formHeadingHtml', 'formBodyHtml', 'crudName', 'crudNameCap', 'modelName', 'viewName', 'routeGroup', 'primaryKey'],
        'form' => ['formFieldsHtml'],
        'create' => ['crudName', 'crudNameCap', 'modelName', 'modelNameCap', 'viewName', 'routeGroup', 'viewTemplateDir'],
        'edit' => ['crudName', 'crudNameSingular', 'crudNameCap', 'modelNameCap', 'modelName', 'viewName', 'routeGroup', 'primaryKey', 'viewTemplateDir'],
        'show' => ['formHeadingHtml', 'formBodyHtml', 'formBodyHtmlForShowView', 'crudName', 'crudNameSingular', 'crudNameCap', 'modelName', 'viewName', 'routeGroup', 'primaryKey'],
        /*
         * Add new stubs templates here if you need to, like action, datatable...
         * custom_template needs to be activated for this to work
         */
    ],

    'themes' => [
        [
            /*
                * These are the theme defaults*/
            "name" => 'default',
            //folder with  with json crud definitions
            ## IMPORTANT ####
            /*
            Name the files with your model name prural or singular;
            to determine migration order , prefix the files with 
            */
            //eg posts.js
            'folder' => "crud/",
            //
            'form-helper' => 'html',
            /*model Namespce. 
            leave empty if your models are in app/ dir
            */
            'model-namespace' => 'Models',
            /*Use softdeletes on the models.*/
            'soft-deletes' => true,
            /*
                use this to create contollers in another namespace
                leave this empty to create contollers in \App\Http\Controllesr
                setting this to 'Admin' will created  a controller in \App\Http\Controllers\Admin\YourController.php
            */
            'controller-namespace' => '',
            'pk' => 'id',  //The name of the primary key on your models.
            'pagination' => '25',
            // use this to add prefix to yoyr routes
            // example: setting this to admin will creates routes under Admin group
            'route-group' => '', //Prefix of the route group
            'force' => false, //Replace Items if they exists
            //your theme MUST be in inertia theme
            'inertia' => false, //Use Inertia for Contollers and and views
            'locales' => 'en', //no|langs  
            /*items to create comma separated
                *possible values : 
                policy,transformer,controller,model,migration,view,route,factory,resource,lang, enums
                use 'all' or null to make everything
                */
            'only' => 'all',

        ],[
            "name" => 'chat',
            'folder' => "crud/chat/",
            'force' => true,
            'form-helper' => 'inertia',
            'only' => 'policy,controller,migration,route,model,factory,resource,lang,enums',
        ],

    ],


    // add your pivote table here in order to create only miration
    'pivot_tables' => [],

    
    //use these to skip generating certian items
    // eg the models Team and User will NOT be geenerated in any theme above
    'skip' => [
        'model' => 'teams,user', // dont create teams and user model
        'controller' => 'user,', // dont create user controller
        'migration' => 'teams,user',  // dont create user migration
        'resource' => null,
        'policy' => null,
        'transformer' => null,
        'view' => null,
        'route' => null,
        'factory' => null,
        'lang' => null,
        'enums' => null
    ]


];
