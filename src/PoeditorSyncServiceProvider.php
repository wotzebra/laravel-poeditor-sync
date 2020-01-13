<?php

namespace NextApps\PoeditorSync;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use NextApps\PoeditorSync\Commands\DownloadCommand;

class PoeditorSyncServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/poeditor-sync.php' => config_path('poeditor-sync.php'),
            ], 'config');

            $this->commands([
                DownloadCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/poeditor-sync.php', 'poeditor-sync');

        $this->app->singleton('poeditor-sync', function () {
            return new PoeditorSyncManager(new Client());
        });
    }
}
