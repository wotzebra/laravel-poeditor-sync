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
            $translations = collect(app(Poeditor::class)->download(is_string($key) ? $key : $locale))
                ->map(function ($translation) {
                    if (is_string($translation)) {
                        return $translation;
                    }

                    return collect($translation)->filter(fn ($value) => $value !== '');
                })
                ->toArray();

            collect($locale)->each(function ($internalLocale) use ($translations) {
                app(TranslationManager::class)->createTranslationFiles(collect($translations), $internalLocale);
            });
        });

        $this->info('All translations have been downloaded!');

        if (config('poeditor-sync.validate_after_download')) {
            $this->call('poeditor:validate');
        }

        return Command::SUCCESS;
    }

    protected function getLocales() : Collection
    {
        return collect(config('poeditor-sync.locales'));
    }
}
