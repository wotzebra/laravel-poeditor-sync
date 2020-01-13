<?php

namespace NextApps\PoeditorSync;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\VarExporter\VarExporter;

class PoeditorSyncManager
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new manager instance.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Download and save translations in all languages.
     *
     * @return void
     */
    public function download()
    {
        $this->createLocaleFolders();

        $this->getLocales()->each(function ($locale) {
            $translations = $this->downloadTranslations($locale);

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
     * Get translations of project in the language.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function downloadTranslations(string $locale)
    {
        $projectResponse = $this->client
            ->post(
                'https://api.poeditor.com/v2/projects/export',
                [
                    'form_params' => [
                        'api_token' => $this->getApiKey(),
                        'id' => $this->getProjectId(),
                        'language' => $locale,
                        'type' => 'key_value_json',
                    ],
                ]
            )
            ->getBody()
            ->getContents();

        $exportUrl = json_decode($projectResponse, true)['result']['url'];

        $exportResponse = $this->client
            ->get($exportUrl)
            ->getBody()
            ->getContents();

        return json_decode($exportResponse, true);
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
        return collect(config('poeditor-sync.locales'));
    }

    /**
     * Get project id.
     *
     * @return string
     */
    protected function getProjectId()
    {
        return config('poeditor-sync.project_id');
    }

    /**
     * Get api key.
     *
     * @return string
     */
    protected function getApiKey()
    {
        return config('poeditor-sync.api_key');
    }
}
