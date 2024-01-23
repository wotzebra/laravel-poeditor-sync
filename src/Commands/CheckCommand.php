<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use NextApps\PoeditorSync\Translations\TranslationManager;

class CheckCommand extends Command
{
    protected $signature = 'poeditor:check
                            {toBeCheckedLocale : The language to check}
                            {compareToLocale? : The language used to compare with}';

    protected $description = 'Check that translations have the same parameters and pluralization';

    public function handle() : mixed
    {
        if ($this->getToBeCheckedLocale() === null) {
            $this->error('Invalid check locale provided!');

            return 1;
        }

        if ($this->getCompareToLocale() === null) {
            $this->error('Invalid compare locale provided!');

            return 1;
        }

        if($this->getToBeCheckedLocale() === $this->getCompareToLocale()) {
            $this->info('languages are the same, nothing to compare');

            return 0;
        }

        app(TranslationManager::class)->checkTranslations($this->getToBeCheckedLocale(), $this->getCompareToLocale());

        $this->info('All translations have been downloaded!');
    }

    protected function getToBeCheckedLocale() : ?string
    {
        $locale = $this->argument('toBeCheckedLocale');

        if (! collect(config('app.supported_locales'))->flatten()->contains($locale)) {
            return null;
        }

        return $locale;
    }

    protected function getCompareToLocale() : ?string
    {
        $locale = $this->argument('compareToLocale') ?? app()->getLocale();

        if (! collect(config('app.supported_locales'))->flatten()->contains($locale)) {
            return null;
        }

        return $locale;
    }
}
