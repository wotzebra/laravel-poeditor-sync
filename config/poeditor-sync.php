<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | The POEditor API key that will be used to authenticate the requests to
    | the POEditor API to upload and download your project's translations.
    |
    */
    'api_key' => env('POEDITOR_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Project ID
    |--------------------------------------------------------------------------
    |
    | The ID of the POEditor project that is associated with this project.
    |
    */
    'project_id' => env('POEDITOR_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | The list of locales that are used in Laravel project. If you provide an
    | associative array, the keys of the array are the POEditor locales,
    | while the values are the locales used in this Laravel project.
    |
    | Example: ['en', 'nl'] OR ['en-gb' => 'en', 'nl-be' => 'nl']
    |
    */
    'locales' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Include vendor
    |--------------------------------------------------------------------------
    |
    | Vendor translations should not always be uploaded / downloaded. This
    | option allows you to toggle the inclusion of vendor translations.
    |
    */
    'include_vendor' => true,

    /*
    |--------------------------------------------------------------------------
    | Exclude files
    |--------------------------------------------------------------------------
    |
    | You may not always want to upload all your translation files. Here you
    | can define the files that should be skipped during upload process.
    |
    */
    'excluded_files' => [],
];
