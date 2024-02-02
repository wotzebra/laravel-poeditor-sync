<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Translations\TranslationManager;

class UploadCommand extends Command
{
    protected $signature = 'poeditor:upload
                            {locale? : The language to upload translations from}
                            {--force : Overwrite the existing POEditor translations}';

    protected $description = 'Upload translations to POEditor';

    public function handle() : int
    {
        if ($this->getLocale() === null) {
            $this->error('Invalid locale provided!');

            return Command::FAILURE;
        }

        $translations = app(TranslationManager::class)->getTranslations($this->getLocale());

        $response = app(Poeditor::class)->upload(
            $this->getPoeditorLocale(),
            $translations,
            $this->hasOption('force') && $this->option('force'),
            false
        );

        $this->info('All translations have been uploaded:');

        $this->line("{$response->getAddedTermsCount()} terms added");
        $this->line("{$response->getDeletedTermsCount()} terms deleted");
        $this->line("{$response->getAddedTranslationsCount()} translations added");
        $this->line("{$response->getUpdatedTranslationsCount()} translations updated");

        $diff = collect(app(Poeditor::class)->download($this->getPoeditorLocale()))->dot()
            ->diff(collect($translations)->dot());

        if ($diff->isEmpty()) {
            $this->info('The translations match the ones on POEditor');
        } else {
            $this->error('The following translations do not match the ones on POEditor:');

            $this->table(['Key', 'Value'], $diff->map(function ($value, $key) {
                return [$key, $value];
            }));

            if ($this->ask('Do you want to clean up the translations on POEditor? (y/n)')) {
                $response = app(Poeditor::class)->upload($this->getPoeditorLocale(), $translations, true, true);

                $this->info("Deleted {$response->getDeletedTermsCount()} terms");
            }
        }

        return COMMAND::SUCCESS;
    }

    protected function getLocale() : ?string
    {
        $locale = $this->argument('locale') ?? app()->getLocale();

        if (! collect(config('poeditor-sync.locales'))->flatten()->contains($locale)) {
            return null;
        }

        return $locale;
    }

    protected function getPoeditorLocale() : string
    {
        $locales = config('poeditor-sync.locales');

        if (Arr::isAssoc($locales)) {
            return collect($locales)->filter(function ($internalLocales) {
                return collect($internalLocales)->contains($this->getLocale());
            })->keys()->first();
        }

        return $this->getLocale();
    }
}
