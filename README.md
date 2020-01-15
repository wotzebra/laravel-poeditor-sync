# Laravel Poeditor Synchronization

> WORK IN PROGRESS!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nextapps/laravel-poeditor-sync.svg?style=flat-square)](https://packagist.org/packages/nextapps-be/laravel-poeditor-sync)
[![Build Status](https://img.shields.io/travis/nextapps/laravel-poeditor-sync/master.svg?style=flat-square)](https://travis-ci.org/nextapps-be/laravel-poeditor-sync)
[![Quality Score](https://img.shields.io/scrutinizer/g/nextapps/laravel-poeditor-sync.svg?style=flat-square)](https://scrutinizer-ci.com/g/nextapps-be/laravel-poeditor-sync)
[![Total Downloads](https://img.shields.io/packagist/dt/nextapps/laravel-poeditor-sync.svg?style=flat-square)](https://packagist.org/packages/nextapps-be/laravel-poeditor-sync)

Upload and download POEditor translations.
Both PHP and JSON translation files are supported.
Vendor PHP / JSON translations can also be uploaded / downloaded.

## Installation

You can install the package via composer:

```bash
composer require nextapps/laravel-poeditor-sync
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

In the 'poeditor-sync' configuration file, you should specify the supported locales:

```php
// in config/poeditor-sync.php

return [
    // ...
    'locales' => ['en', 'nl', 'fr'],
    // ...
]
```

## Usage

### Download translations

``` bash
// Download translations in all locales
php artisan poeditor:download
```

### Upload Translations

``` bash
// Upload translations of default app locale
php artisan poeditor:upload

// Upload translations of specified locale
php artisan poeditor:upload nl

// Upload translations and overwrite existing POEditor translations
php artisan poeditor:upload --force
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Günther Debrauwer](https://github.com/nextapps)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
