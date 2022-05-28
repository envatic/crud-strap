

### Why :

Thius crud generator allows you too create a ready to go site with a single command
`php artisan crud:strap`
This will make
`policy, transformer, controller, model, migration, view, route, factory,resource, lang, enums`.

# .json files
The command use `.json` files found in `crud` folder  to determine how to generate the tables and crud;
example file schema is shown below;
> Evey json files represents a table and its crud
```json
{
	"fields": [
		{
			"name": "user_id",
			"type": "foreignId|constrained|onUpdate:'cascade'|onDelete:'cascade'",
			"rules": "required|integer|exists:users,id",
		},
		{
			"name": "name",
			"type": "string",
			"rules": "required|string"
		},

		{
			"name": "active",
			"type": "boolean|default:true",
			"rules": "required|boolean"
		}
	],
	"relationships": [
		{
			"name": "user",
			"type": "belongsTo",
            "class": "User|user_id|id"
		},
        {
			"name": "posts",
			"type": "hasMany",
            "class":"Post,bot_id,id",
		}
	]
}

```

# Field:
array of field;
```json
    {
		"name": "user_id",
		"type": "foreignId|constrained|onUpdate:'cascade'|onDelete:'cascade'",
		"rules": "required|integer|exists:users,id"
	},
```
# name:
The name of the table column to be created. The same name will be used to create the form input fields if  `rules` if added;

# type
The database field type;
the valid field types are [documented here](fields.md)
You can also see how the `database fields` are resolved are converted to `form fields` [here](fields.md#conversions-to-form-fields ) and ho w to obverride them;
> syntax
The laravel valaidation syntax is adopted;
`foreignId|constrained|onUpdate:'cascade'|onDelete:'cascade'`
resolves to
`foreignId()->constrained()->onUpdate('cascade')->onDelete('cascade')`
use a pipe `|` to separate functions
and a `:` to pass variables to functions
to passing `strings` to the function it requires `quotes` to be used
EG: `string|default:'new'` resolves to `string()->default('new')`

> another examples

`decimal:16,8|nullable|default:0` represents this `decimal(16,8)->nullable()->default(0)`

`boolean|default:false` represents this `boolean()->default(false)`

# rules
The form validation rules fro this field in controller
> fields without validation will not be used in forms

	
# relationships array:

```json
       {
			"name": "posts",
			"type": "hasMany",
			"class": "Posts|bot_id|id"
		}
```




This config is set in `app/configs/crudstrap.php`
Every theme will have a config of its own
This is to allow you to create configs for several parts of your app
Eg `Frontend` and `Backend`

```php
.... 
   'thems'=>[
       [ 
            "name" => 'frontend',
            'folder' => "crud/frontend",
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
        ]
   ]
.....
```
This cofig can be overriden individual json crud files;
see [#Overiding]('#Overiding')
 # name
 This determine the name of the config to user when generation;
 In the example above, to use this config
 
 run `php artisan crud:strap frontend`

 All crud files located in app in folder `crud/frontend` will be generated acording to the config;

 # folder
 This determine the folder where the crud json files are located;
 In the example above, to use this config
 default is `crud` ( app_path('crud'));
 
 run `php artisan crud:strap frontend`
 # model-namespace 
 The namespace of the model  - sub directories will be created
 # model-namespace 
 The namespace that the model will be placed in - directories will be created
 # pk
 The name of the primary key for the tables
 # pagination (only for blade templates )
 The amount of models per page for index pages| Option 
# route-group
Prefix of the route group
# view-path 
> default `{theme}`
This is the name of the view path;
The default location the the name of the theme. Eg if `name` is set as `backend` as  seen in the the config above, view will generated at `resources/views/backend` if you want to generate in the roots of view sfolder set it as 
```php
    //generate view files in  resources/views
    ...
    "view-path" =>''
    ....
```
# form-helper
> default `html`
Helper for the form ALOWED `html or laravelcollective`
# locales
The Languages (Locales) supported by your app  e.g. locales=en,fr,de
# soft-deletes 
 `true | false`
 > default `true`
Include soft deletes fields. eg ` 'soft-deletes' => true`
 # force 
  `true | false`,
  > default `true`
  Delete files files if exists. Will delete existing files before generating a new file. Eg if UserModel `app/Models/User.php` is found when attempting to generate a another UserModel file, the file will be deleted

# inertia
 `true | false`,
  > default `false`
  Create inertia contollers and views
 
 # only
This determinde what files to build
comma separted list containing any of `all,policy,transformer,controller,model,migration,view,route,factory,resource,lang, enums`,
if set to all, everythin will be generated
  > default `all`

Eg:`only' => migration,view,route`;

# Overiding the configs in each crud
Create a config option with the itesm you wish to overiide
For example to generate only a migration for this paticular crud.json file
we have overiddem `only` option;
```json
{
    "config": {
         /*"name": "default",
         "folder": "crud/",
         "form-helper": "html",
         "model-namespace": "Models",
         "soft-deletes": true,
         "controller-namespace":null,
         "pk": "id",
         "pagination": 25,
         "route-group": null,
         "force": false,
         "inertia": false,
         "locales": "en",*/
         "only": "migration"
    },

	"fields": [
		
		{
			"name": "name",
			"type": "string",
			"rules": "required|string"
		},
		{
			"name": "email",
			"type": "timestamp|nullable",
            "rules": "required|string|unique"
		},
        {
            "name": "email_verified_at",
            "type": "string|nullable"
        }, {
            "name": "password",
            "type": "string",
            "rules": "required|string"
        },
        {
            "type": "rememberToken"
        },
        {
            "name": "current_team_id",
            "type": "foreignId"
        },
        {
            "name": "profile_photo_path",
            "type": "string|nullable"
        }
	]

}

```