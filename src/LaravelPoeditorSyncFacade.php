<?php

namespace Nextapps\LaravelPoeditorSync;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nextapps\LaravelPoeditorSync\Skeleton\SkeletonClass
 */
class LaravelPoeditorSyncFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-poeditor-sync';
    }
}
