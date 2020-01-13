<?php

namespace NextApps\PoeditorSync;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use Symfony\Component\VarExporter\VarExporter;

class PoeditorSyncManager
{
    /**
     * Download and save translations in all languages.
     *
     * @return void
     */
    public function download()
    {
        $this->createLocaleFolders();

        $this->getLocales()->each(function ($locale) {
            $translations = app(Poeditor::class)->getTranslations($locale);

            $this->createPhpTranslationFiles($translations, $locale);
            $this->createJsonTranslationFile($translations, $locale);
        });
    }

    /**
     * Create PHP translation files containing arrays.
     *
     * @param array $translations
     * @param string $locale
     * @param null|string $path
     *
     * @return void
     */
    protected function createPhpTranslationFiles(array $translations, string $locale, string $path = null)
    {
        foreach ($translations as $key => $translation) {
            if (is_string($translation)) {
                continue;
            }

            $array = VarExporter::export($translation);

            file_put_contents(
                resource_path("lang/{$locale}/{$key}.php"),
                '<?php' . PHP_EOL . PHP_EOL . "return {$array};",
            );
        }
    }

    /**
     * Create JSON translation file containing arrays.
     *
     * @param array $translations
     * @param string $locale
     * @param null|string $path
     *
     * @return void
     */
    protected function createJsonTranslationFile(array $translations, string $locale, string $path = null)
    {
        $json = Arr::where($translations, function ($translation) {
            return is_string($translation);
        });

        if (empty($json)) {
            return;
        }

        file_put_contents(
            resource_path("lang/{$locale}.json"),
            json_encode($json, JSON_PRETTY_PRINT)
        );
    }



    /**
     * Create folders for all project locales in "lang" resources folder.
     *
     * @return void
     */
    protected function createLocaleFolders()
    {
        if (! file_exists(resource_path('lang'))) {
            mkdir(resource_path('lang'));
        }

        $this->getLocales()->each(function ($locale) {
            $path = resource_path("lang/{$locale}/");

            if (file_exists($path)) {
                return;
            }

            mkdir($path);
        });
    }

    /**
     * Get project locales.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getLocales()
    {
        return collect(config('poeditor-sync.languages'));
    }
}
