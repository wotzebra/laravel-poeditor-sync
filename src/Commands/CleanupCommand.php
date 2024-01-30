<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use NextApps\PoeditorSync\Translations\TranslationManager;

class CleanupCommand extends Command
{
    protected $signature = 'poeditor:cleanup';

    protected $description = 'Cleanup unused translations and run update command if translations don\'t exist anymore.';

    public function handle() : mixed
    {
        // remove empty string translations
        app(TranslationManager::class)->removeEmptyTranslations();

        // run poeditor:update command if translations don't exist anymore + ask confirmation
        if (app(TranslationManager::class)->translationsDontExistAnymore()
            && $this->confirm('Some translations don\'t exist anymore. Do you want to run poeditor:update command?')
        ) {
            $this->call('poeditor:update');
        }
    }
}
