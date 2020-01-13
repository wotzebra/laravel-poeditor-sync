<?php

namespace NextApps\PoeditorSync;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NextApps\PoeditorSync\PoeditorSyncManager
 */
class PoeditorSync extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'poeditor-sync';
    }
}
