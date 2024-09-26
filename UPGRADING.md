# Upgrading

## From v2 to v3

- Install `wotz/laravel-poeditor-sync` instead of `nextapps/laravel-poeditor-sync`
- Replace all occurrences of `NextApps\PoeditorSync` namespace with new `Wotz\PoeditorSync` namespace

## From v1 to v2

- Translation keys with '.' in the key i.e. 'user.name' will result in duplicate translation entries after upload and download from poeditor. To prevent this convert all keys with '.' to arrays.
