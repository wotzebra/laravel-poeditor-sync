<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Translations\TranslationManager;

class CheckTranslationsCommand extends Command
{
    protected $signature = 'poeditor:check';

    protected $description = 'Check if local translations match the ones on POEditor';

    public function handle() : int
    {
        $this->getLocales()->each(function ($locale, $key) {
            $translations = app(Poeditor::class)->download(is_string($key) ? $key : $locale);

            collect($locale)->each(function ($internalLocale) use ($translations) {
                $this->info("Checking translations for {$internalLocale}");
                $valid = app(TranslationManager::class)->checkTranslations(collect($translations), $internalLocale);

                if (! $valid) {
                    $this->error("The translations for {$internalLocale} do not match the ones on POEditor");
                }
            });
        });

        return Command::SUCCESS;
    }

    protected function getLocales() : Collection
    {
        return collect(config('poeditor-sync.locales'));
    }
}
