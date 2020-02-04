<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Translations\TranslationManager;

class UploadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poeditor:upload
                            {locale? : The language to upload translations from}
                            {--force : Overwrite the existing POEditor translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload translations to POEditor';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->getLocale() === null) {
            $this->error('Invalid locale provided!');

            return 1;
        }

        $translations = app(TranslationManager::class)->getTranslations($this->getLocale());

        $response = app(Poeditor::class)->upload(
            $this->getPoeditorLocale(),
            $translations,
            $this->hasOption('force') && $this->option('force')
        );

        $this->info('All translations have been uploaded:');

        $this->line("{$response->getAddedTermsCount()} terms added");
        $this->line("{$response->getDeletedTermsCount()} terms deleted");
        $this->line("{$response->getAddedTranslationsCount()} translations added");
        $this->line("{$response->getUpdatedTranslationsCount()} translations updated");
    }

    /**
     * Get locale that needs to be used to upload translations.
     *
     * @return null|string
     */
    protected function getLocale()
    {
        $locale = $this->argument('locale') ?? app()->getLocale();

        if (! in_array($locale, config('poeditor-sync.locales'))) {
            return;
        }

        return $locale;
    }

    /**
     * Get POEditor locale.
     *
     * @return string
     */
    protected function getPoeditorLocale()
    {
        $locales = config('poeditor-sync.locales');

        if (Arr::isAssoc($locales)) {
            return array_flip($locales)[$this->getLocale()];
        }

        return $this->getLocale();
    }
}
