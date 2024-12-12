# Changelog

All notable changes to `laravel-poeditor-sync` will be documented in this file

## 3.1.0 - 2024-12-12

- Added: PHP 8.4 support (https://github.com/wotzebra/laravel-poeditor-sync/pull/30)

## 3.0.1 - 2024-12-02

- Added: Allow ignoring certain translation keys when running validate command (https://github.com/wotzebra/laravel-poeditor-sync/pull/29)

## 3.0.0 - 2024-09-26

- Changed: Namespace switch from 'NextApps' to 'Wotz' (https://github.com/wotzebra/laravel-poeditor-sync/pull/28)

## 2.0.0 - 2024-04-05

- Added: Command to validate translations (https://github.com/nextapps-be/laravel-poeditor-sync/pull/20)
- Added: Command to check translation status (https://github.com/nextapps-be/laravel-poeditor-sync/pull/23, https://github.com/nextapps-be/laravel-poeditor-sync/pull/26)
- Added: Run cleanup command after upload and ask to cleanup terms in poeditor (https://github.com/nextapps-be/laravel-poeditor-sync/pull/24)
- Changed: Simplify tests by @gdebrauwer in (https://github.com/nextapps-be/laravel-poeditor-sync/pull/21)
- Changed: Remove empty translation strings on poeditor download (https://github.com/nextapps-be/laravel-poeditor-sync/pull/22)

## 1.0.1 - 2024-03-15

- Added: Laravel 11 support ([#27](https://github.com/wotzebra/laravel-poeditor-sync/pull/27))

## 1.0.0 - 2024-01-23

- Changed: Modernize codebase ([#16](https://github.com/wotzebra/laravel-poeditor-sync/pull/16))
- Removed: Drop support for PHP 7.4 until 8.0  and for Laravel 7 and 8 ([#15](https://github.com/wotzebra/laravel-poeditor-sync/pull/15))
- Removed: Docblocks (add typehints instead) ([#15](https://github.com/wotzebra/laravel-poeditor-sync/pull/15))

## 0.8.1 - 2024-01-19

- Fixed: Do not delete excluded translation files when downloading poeditor translations ([#14](https://github.com/wotzebra/laravel-poeditor-sync/pull/14))

## 0.8.0 - 2023-12-15

- Added: PHP 8.3 support ([#13](https://github.com/wotzebra/laravel-poeditor-sync/pull/13))

## 0.7.1 - 2023-10-26

- Fix PHP deprecation warning in string concatenation ([#12](https://github.com/wotzebra/laravel-poeditor-sync/pull/12))

## 0.7.0 - 2023-08-25

- Add support for mapping multiple internal locales to the same POEditor locale ([#11](https://github.com/wotzebra/laravel-poeditor-sync/pull/11))

## 0.6.0 - 2022-04-05

- Add PHP 8.2 and Laravel 10 support ([#10](https://github.com/wotzebra/laravel-poeditor-sync/pull/10))

## 0.5.0 - 2022-02-16

- Add PHP 8.1 and Laravel 9 support ([#9](https://github.com/wotzebra/laravel-poeditor-sync/pull/9))

## 0.4.1 - 2021-07-31

 - Prevent unwanted creation of empty 'vendor.php' file when vendor translations are present ([#8](https://github.com/wotzebra/laravel-poeditor-sync/pull/8))

## 0.4.0 - 2021-02-15

 - Add php8 support and switch to github actions ([#6](https://github.com/wotzebra/laravel-poeditor-sync/pull/6))

## 0.3.0 - 2020-10-09

 - Do not create JSON translations file if it will be empty ([#4](https://github.com/wotzebra/laravel-poeditor-sync/pull/4))
 - Add option to exclude files from upload ([#3](https://github.com/wotzebra/laravel-poeditor-sync/pull/3))
 - Throw "invalid argument" exception if API key or Project ID is empty ([#2](https://github.com/wotzebra/laravel-poeditor-sync/pull/2))

## 0.2.0 - 2020-09-09

- Add Laravel 8 support, and drop Laravel 6 support ([#5](https://github.com/wotzebra/laravel-poeditor-sync/pull/5))

## 0.1.0 - 2020-05-08

- Initial release
