# Upgrading

## From v1 to v2

- Translation keys with '.' in the key i.e. 'user.name' will result in duplicate translation entries after upload and download from poeditor. To prevent this convert all keys with '.' to arrays.
