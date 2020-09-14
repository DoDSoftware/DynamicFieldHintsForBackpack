# Dynamic Field Hints for Backpack 4

This package extends the [Backpack for Laravel](https://backpackforlaravel.com/) CrudPanel providing the ability to dynamically set the 'hint' value for its [CRUD fields](https://backpackforlaravel.com/docs/4.1/crud-fields#optional-field-attributes-for-presentation-purposes) by pulling the "comment" for the related column in the database if it exists.

### Notes
- hints are only set if the method is called
- hints are only set if the column in the db has a Comment
- hints are **not** set if a hint already exists on the field
- hints are **not** set if "hint" on the field is set to an empty string


## Installation

Via Composer

``` bash
composer require dodsoftware/dynamic-field-hints-for-backpack
```

If you've disabled Laravel's auto package discovery, you'll need to also add the below to your app's `config/app.php` file

``` 
'providers' => [
    // ...
    DoDSoftware\DynamicFieldHintsForBackpack\AddonServiceProvider::class,
];
``` 

## Usage

Inside your custom CrudController:

```php
    $this->crud->addFields($fields);
    $this->crud->setFieldHintsFromColumnComments();
```

## Supported Databases
- MySQL 5.6+
- PostgreSQL 9.4+
- SQL Server 2017+

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](CONTRIBUTING.md) for details and a todolist.

## Security

If you discover any security related issues, please email [the author](composer.json) instead of using the issue tracker.

## Credits

- [Wesley Smith (DoDSoftware)](https://github.com/DoDSoftware) - creator
- [Cristian Tabacitu](https://github.com/tabacitu) - idea to make this an addon
- [Backpack For Laravel](https://backpackforlaravel.com/) - for making this package possible (and just being awesome in general)


## License

MIT. Please see the [license file](license.md) for more information.
