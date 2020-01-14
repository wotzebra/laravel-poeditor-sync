<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
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
                            {locale : The language to upload translations from}
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
        $translations = app(TranslationManager::class)->getTranslations($this->getLocale());

        app(Poeditor::class)->setTranslations(
            $this->getLocale(),
            $translations,
            $this->hasOption('force') && $this->option('force')
        );

        $this->info('All translations have been uploaded!');
    }

    /**
     * Get locale that needs to be used to upload translations.
     *
     * @return string
     */
    protected function getLocale()
    {
        return $this->argument('locale');
    }
}
