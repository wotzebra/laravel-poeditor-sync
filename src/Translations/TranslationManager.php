<?php

namespace NextApps\PoeditorSync\Translations;

use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\VarExporter\VarExporter;

class TranslationManager
{
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Create a new manager instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get translations of PHP and JSON translation files in the specified locale.
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
        $this->createEmptyLocaleFolder($locale);

        $this->createPhpTranslationFiles($translations, $locale);
        $this->createJsonTranslationFile($translations, $locale);
    }

    /**
     * Get translations of PHP translation files in the specified locale.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function getPhpTranslations(string $locale)
    {
        return collect($this->filesystem->files(resource_path("lang/{$locale}")))
            ->mapWithKeys(function ($file) {
                return [
                    pathinfo($file->path, PATHINFO_FILENAME) => $this->filesystem->getRequire($file->path),
                ];
            })
            ->toArray();
    }

    /**
     * Get translations of JSON translation files in the specified locale.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function getJsonTranslations(string $locale)
    {
        $filename = resource_path("lang/{$locale}.json");

        if (! $this->filesystem->exists($filename)) {
            return [];
        }

        return json_decode($this->filesystem->get($filename), true);
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
        foreach ($translations as $key => $translation) {
            if (is_string($translation)) {
                continue;
            }

            $array = VarExporter::export($translation);

            $this->filesystem->put(
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
        $json = Arr::where($translations, function ($translation) {
            return is_string($translation);
        });

        if (empty($json)) {
            return;
        }

        $this->filesystem->put(
            resource_path("lang/{$locale}.json"),
            json_encode($json, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Create empty folder for locale in "lang" resources folder (if folder does not exist yet).
     *
     * @param string $locale
     *
     * @return void
     */
    protected function createEmptyLocaleFolder(string $locale)
    {
        if (! $this->filesystem->exists(resource_path('lang'))) {
            $this->filesystem->makeDirectory(resource_path('lang'));
        }

        $path = resource_path("lang/{$locale}/");

        if (file_exists($path)) {
            $this->filesystem->deleteDirectory($path);
        }

        $this->filesystem->makeDirectory($path);
    }
}
