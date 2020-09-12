# Dynamic Field Hints for Backpack 4


This package provides automatic field hints for the [Backpack for Laravel](https://backpackforlaravel.com/) administration panel. 
 
- Dynamic hints are only set if the method is called
- Dynamic hints are only set if the column in the db has a Comment
- Dynamic hints are not set if a hint already exists on the field
- Dynamic hints are not set if "hint" on the field is set to an empty string


## Installation

Via Composer

``` bash
composer require digitallyhappy/toggle-field-for-backpack
```

## Usage

Inside your custom CrudController:

```php
$this->crud->addField([
    'name' => 'agreed',
    'label' => 'I agree to the terms and conditions',
    'type' => 'toggle',
    'view_namespace' => 'toggle-field-for-backpack::fields',
]);
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email [the author](composer.json) instead of using the issue tracker.

## Credits

- [Wesley Smith (DoDSoftware)](https://github.com/DoDSoftware) - creator;
- [Cristian Tabacitu](https://github.com/tabacitu) - idea to make this an addon;


## License

MIT. Please see the [license file](license.md) for more information.
