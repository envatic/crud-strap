# Laravel CRUD Generator

Advanced crud Generator for laravel
Generates the following;

```
Model (plus auto relationships, and  casts)
Form validation ( In controller)
Inertia (vue3) Controller or
ApiResource
Database migration
Routes
Policy
Inertia views (vue3)
php enums
```

## Requirements

    Laravel >= 11
    PHP >= 8.3

## Installation

```
composer require envatic/crud-strap --dev
```

## Step by Step

1. Install crud-strap

```
composer require envatic/crud-strap --dev
```

2. Publish Assets

```
php artisan vendor:publish
```

choose the crudstrap service provider

3. Create a folder for your theme inside /crud folder eg /crud/admin/ in your laravel app
4. Add your crudjson files in this folder
   Example file for projects table.

```json
{
	"fields": [
		{
			"name": "user_id",
			"type": "foreignId|constrained|onUpdate:'cascade'|onDelete:'cascade'",
			"rules": "required|integer|exists:users,id"
		},
		{
			"name": "uuid",
			"type": "uuid"
		},
		{
			"name": "name",
			"type": "string",
			"rules": "required|string"
		},
		{
			"name": "slug",
			"type": "string",
			"rules": "required|string"
		},
		{
			"name": "description",
			"type": "text|nullable",
			"rules": "required|string"
		},
		{
			"name": "status",
			"type": "radioselect|default:'pending'",
			"options": {
				"pending": "Under Review",
				"published": "Publish To Website",
				"hidden": "Hide from Users",
				"rejected": "Reject Project"
			},
			"rules": "required|string"
		},
		{
			"name": "rank",
			"type": "integer",
			"rules": "required|string"
		},
		{
			"name": "verified_at",
			"type": "timestamp|nullable"
		},
		{
			"name": "promoted_at",
			"type": "timestamp|nullable"
		}
	],
	"relationships": [
		{
			"name": "user",
			"type": "belongsTo",
			"class": "User|user_id|id"
		},
		{
			"name": "uploads",
			"type": "morphMany",
			"class": "Upload|uploadable"
		},
		{
			"name": "logo",
			"type": "morphOne|where:'key','logo'",
			"class": "Upload|uploadable"
		}
	]
}
```

5. Update `config/crudstrap.php` config file to include your new theme

```php
 [
    "name" => 'admin',
    "view-path" => 'admin',
    'folder' => "crud/admin",
    'force' => true,
    'model-namespace' => 'Models',
    'only' => 'policy,controller,model,migration,route,factory,resource,enums',
],
```

6. run

```php
    php artisan crud:strap admin
```

## License

This project is licensed under the MIT License - see the [License File](LICENSE) for details
