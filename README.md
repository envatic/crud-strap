# CrudStrap

CrudStrap is a powerful Laravel package that automates the creation of CRUD (Create, Read, Update, Delete) operations for your application. It generates controllers, models, views, migrations, and other necessary files based on your specifications, saving you time and effort in the development process.

## Features

-   Generates CRUD operations for Laravel applications
-   Creates controllers, models, views, migrations, and more
-   Supports Inertia.js and Vue.js for frontend
-   Customizable templates and configurations
-   Handles relationships, validations, and file uploads
-   Supports enum creation and usage
-   Generates API resources and policies

## Installation

To install CrudStrap, run the following command in your Laravel project:

```bash
composer require envatic/crudstrap
```

After installation, publish the package assets:

```bash
php artisan vendor:publish --provider="Envatic\CrudStrap\CrudStrapServiceProvider"
```

## Configuration

The package configuration file is located at `config/crudstrap.php`. You can customize various aspects of the CRUD generation process, including:

-   Custom templates
-   Namespace settings
-   Pagination
-   Route groups
-   Localization

## Usage

To generate CRUD files, use the `crud:strap` command:

```bash
php artisan crud:strap {theme}
```

Replace `{theme}` with the name of your desired theme configuration from the `config/crudstrap.php` file.

### Creating CRUD JSON Files

Before running the command, create JSON files in your specified crud folder (default is `crud/`) to define your CRUD structures. Example:

```json
{
	"fields": [
		{
			"name": "title",
			"type": "string",
			"rules": "required|max:255"
		},
		{
			"name": "content",
			"type": "text",
			"rules": "required"
		}
	],
	"relationships": [
		{
			"name": "author",
			"type": "belongsTo",
			"class": "User"
		}
	]
}
```

### Available Commands

-   `crud:delete {theme}`: Remove generated CRUD files

## Frontend Components

CrudStrap provides various Vue components to use in your views:

-   ConfirmationModal
-   FormInput
-   FormLabel
-   FormTextArea
-   Loading
-   LogoInput
-   Modal
-   Pagination
-   PrimaryButton
-   RadioCards
-   RadioSelect
-   SearchInput
-   Switch
-   VueIcon

These components are automatically published to your project's components directory.

## Customization

You can customize the generated files by modifying the stubs in the `resources/crud-strap/` directory after publishing the package assets.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

CrudStrap is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
