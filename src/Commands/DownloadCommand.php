<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Translations\TranslationManager;

class DownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poeditor:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download translations from POEditor';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getLocales()->each(function ($locale, $key) {
            $translations = app(Poeditor::class)->download(is_string($key) ? $key : $locale);

            app(TranslationManager::class)->createTranslationFiles($translations, $locale);
        });

        $this->info('All translations have been downloaded!');
    }

    /**
     * Get project locales.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getLocales()
    {
        return collect(config('poeditor-sync.locales'));
    }
}
