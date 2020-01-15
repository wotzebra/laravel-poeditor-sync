<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use NextApps\PoeditorSync\PoeditorSyncServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use WithFaker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('poeditor-sync.api_key', Str::random());
        config()->set('poeditor-sync.project_id', Str::random());
        config()->set('poeditor-sync.locales', ['en']);
        config()->set('poeditor-sync.include_vendor', true);
    }

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
