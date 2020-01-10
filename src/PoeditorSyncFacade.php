<?php

namespace NextApps\PoeditorSync;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NextApps\PoeditorSync\Skeleton\SkeletonClass
 */
class PoeditorSyncFacade extends Facade
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
