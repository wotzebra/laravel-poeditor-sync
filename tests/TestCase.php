<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use NextApps\PoeditorSync\PoeditorSyncServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use WithFaker;

    /**
     * Register package providers.
     *
     * @param mixed $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            PoeditorSyncServiceProvider::class,
        ];
    }
}
