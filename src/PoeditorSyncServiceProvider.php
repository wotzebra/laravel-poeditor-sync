<?php

namespace NextApps\PoeditorSync;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use NextApps\PoeditorSync\Commands\DownloadCommand;
use NextApps\PoeditorSync\Commands\UploadCommand;
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
                __DIR__ . '/../config/poeditor-sync.php' => config_path('poeditor-sync.php'),
            ], 'config');

            $this->commands([
                DownloadCommand::class,
                UploadCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/poeditor-sync.php', 'poeditor-sync');

        $this->app->bind(Poeditor::class, function () {
            return new Poeditor(
                app(Client::class),
                config('poeditor-sync.api_key'),
                config('poeditor-sync.project_id')
            );
        });
    }
}
