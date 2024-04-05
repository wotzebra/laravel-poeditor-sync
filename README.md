# Laravel Poeditor Synchronization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nextapps/laravel-poeditor-sync.svg?style=flat-square)](https://packagist.org/packages/nextapps/laravel-poeditor-sync)
[![Total Downloads](https://img.shields.io/packagist/dt/nextapps/laravel-poeditor-sync.svg?style=flat-square)](https://packagist.org/packages/nextapps/laravel-poeditor-sync)

Upload and download POEditor translations.
Both PHP and JSON translation files are supported.
Vendor PHP / JSON translations can also be uploaded / downloaded.

## Installation

You can install the package via composer:

```bash
composer require nextapps/laravel-poeditor-sync --dev
```

You can publish the configuration file:

```bash
php artisan vendor:publish --provider="NextApps\PoeditorSync\PoeditorSyncServiceProvider"
```

Set the POEditor API key and Project ID in your env-file:
```
POEDITOR_API_KEY=<your api key>
POEDITOR_PROJECT_ID=<your project id>
```

In the 'poeditor-sync' configuration file, you should specify the supported locales.
You can also provide an associate array, if you want to map POEditor locales to internal locales.

```php
// in config/poeditor-sync.php

// Provide array with all supported locales ...
'locales' => ['en', 'nl', 'fr'],

// ... Or provide associative array with POEditor locales mapped to internal locales
'locales' => ['en-gb' => 'en', 'nl-be' => 'nl'],

// ... Or you can map multiple internal locales to the same POEditor locale
'locales' => ['nl' => ['nl_BE', 'nl_NL']],
```

## Usage

### Download translations

All translations in all supported locales will be downloaded.

``` bash
php artisan poeditor:download
```

### Upload Translations

Upload translations of the default app locale:

``` bash
php artisan poeditor:upload
```

Upload translations of specified locale:

```bash
php artisan poeditor:upload nl
````

Upload translations and overwrite existing POEditor translations:

```bash
php artisan poeditor:upload --force
```

### Validate Translations

Validate that translations have the same replacements and correct pluralization:

```bash
php artisan poeditor:validate
```

### Status of Translations

Check if local translations match the ones on POEditor:

```bash
php artisan poeditor:status
```

## Testing

``` bash
composer test
```

## Linting

```bash
composer lint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Günther Debrauwer](https://github.com/nextapps)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
