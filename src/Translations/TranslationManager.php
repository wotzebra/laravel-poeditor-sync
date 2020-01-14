<?php

namespace NextApps\PoeditorSync\Translations;

use Illuminate\Support\Arr;
use Symfony\Component\VarExporter\VarExporter;

class TranslationManager
{
    /**
     * Get translations of PHP and JSON translation files in the specified language.
     *
     * @param string $locale
     *
     * @return array
     */
    public function getTranslations(string $locale)
    {
        return array_merge(
            $this->getPhpTranslations($locale),
            $this->getJsonTranslations($locale)
        );
    }

    /**
     * Create translation files based on the provided array.
     *
     * @param array $translations
     * @param string $locale
     *
     * @return void
     */
    public function createTranslationFiles(array $translations, string $locale)
    {
        $this->createPhpTranslationFiles($translations, $locale);
        $this->createJsonTranslationFile($translations, $locale);
    }

    /**
     * Get translations of PHP translation files in the specified language.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function getPhpTranslations(string $locale)
    {
        $filenames = array_diff(
            scandir(resource_path("lang/{$locale}")),
            ['.', '..']
        );

        return collect($filenames)
            ->mapWithKeys(function ($filename) use ($locale) {
                return [
                    pathinfo($filename, PATHINFO_FILENAME) => require resource_path("lang/{$locale}/{$filename}"),
                ];
            })
            ->toArray();
    }

    /**
     * Get translations of JSON translation files in the specified language.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function getJsonTranslations(string $locale)
    {
        $filename = resource_path("lang/{$locale}.json");

        if (! file_exists($filename)) {
            return [];
        }

        return json_decode(file_get_contents($filename), true);
    }

    /**
     * Create PHP translation files containing arrays.
     *
     * @param array $translations
     * @param string $locale
     *
     * @return void
     */
    protected function createPhpTranslationFiles(array $translations, string $locale)
    {
        $this->createLocaleFolder($locale);

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
     *
     * @return void
     */
    protected function createJsonTranslationFile(array $translations, string $locale)
    {
        $this->createLocaleFolder($locale);

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
     * Create folder for locale in "lang" resources folder (if folder does not exist yet).
     *
     * @param string $locale
     *
     * @return void
     */
    protected function createLocaleFolder(string $locale)
    {
        if (! file_exists(resource_path('lang'))) {
            mkdir(resource_path('lang'));
        }

        $path = resource_path("lang/{$locale}/");

        if (file_exists($path)) {
            return;
        }

        mkdir($path);
    }
}
