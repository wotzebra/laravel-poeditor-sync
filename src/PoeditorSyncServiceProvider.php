<?php

namespace NextApps\PoeditorSync;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use NextApps\PoeditorSync\Commands\DownloadCommand;
use NextApps\PoeditorSync\Poeditor\Poeditor;

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
            return new PoeditorSyncManager();
        });

        $this->app->bind(Poeditor::class, function () {
            return new Poeditor(
                new Client(),
                config('poeditor-sync.api_key'),
                config('poeditor-sync.project_id')
            );
        });
    }
}
