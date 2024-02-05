<?php

namespace NextApps\PoeditorSync;

use Illuminate\Support\ServiceProvider;
use NextApps\PoeditorSync\Commands\DownloadCommand;
use NextApps\PoeditorSync\Commands\UploadCommand;
use NextApps\PoeditorSync\Commands\ValidateCommand;
use NextApps\PoeditorSync\Poeditor\Poeditor;

class PoeditorSyncServiceProvider extends ServiceProvider
{
    public function boot() : void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/poeditor-sync.php' => config_path('poeditor-sync.php'),
            ], 'config');

            $this->commands([
                DownloadCommand::class,
                UploadCommand::class,
                ValidateCommand::class,
            ]);
        }
    }

    public function register() : void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/poeditor-sync.php', 'poeditor-sync');

        $this->app->bind(Poeditor::class, function () {
            return new Poeditor(
                config('poeditor-sync.api_key'),
                config('poeditor-sync.project_id')
            );
        });
    }
}
