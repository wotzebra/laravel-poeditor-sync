<?php

namespace Wotz\PoeditorSync;

use Illuminate\Support\ServiceProvider;
use Wotz\PoeditorSync\Commands\DownloadCommand;
use Wotz\PoeditorSync\Commands\StatusCommand;
use Wotz\PoeditorSync\Commands\UploadCommand;
use Wotz\PoeditorSync\Commands\ValidateCommand;
use Wotz\PoeditorSync\Poeditor\Poeditor;

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
                StatusCommand::class,
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
