<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Translations\TranslationManager;

class DownloadCommand extends Command
{
    protected $signature = 'poeditor:download';

    protected $description = 'Download translations from POEditor';

    public function handle() : int
    {
        $this->getLocales()->each(function ($locale, $key) {
            $translations = app(Poeditor::class)->download(is_string($key) ? $key : $locale);

            collect($locale)->each(function ($internalLocale) use ($translations) {
                app(TranslationManager::class)->createTranslationFiles(collect($translations), $internalLocale);
            });
        });

        $this->info('All translations have been downloaded!');

        return Command::SUCCESS;
    }

    protected function getLocales() : Collection
    {
        return collect(config('poeditor-sync.locales'));
    }
}
