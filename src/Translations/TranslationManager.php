<?php

namespace NextApps\PoeditorSync\Translations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\VarExporter\VarExporter;

class TranslationManager
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getTranslations(string $locale) : array
    {
        $translations = array_merge(
            $this->getPhpTranslations($this->getLangPath("/{$locale}")),
            $this->getJsonTranslations($this->getLangPath("/{$locale}.json")),
        );

        if (config('poeditor-sync.include_vendor')) {
            $translations += $this->getVendorTranslations($locale);
        }

        return $translations;
    }

    public function createTranslationFiles(array $translations, string $locale) : void
    {
        $this->createEmptyLocaleFolder($locale);

        $this->createPhpTranslationFiles($translations, $locale);
        $this->createJsonTranslationFile($translations, $locale);

        if (config('poeditor-sync.include_vendor')) {
            $this->createVendorTranslationFiles($translations, $locale);
        }
    }

    protected function getVendorTranslations(string $locale) : array
    {
        if (! $this->filesystem->exists($this->getLangPath('vendor'))) {
            return [];
        }

        $directories = collect($this->filesystem->directories($this->getLangPath('vendor')));

        $translations = $directories->mapWithKeys(function ($directory) use ($locale) {
            $phpTranslations = $this->getPhpTranslations("{$directory}/{$locale}");
            $jsonTranslations = $this->getJsonTranslations("{$directory}/{$locale}.json");

            return [basename($directory) => array_merge($phpTranslations, $jsonTranslations)];
        })->toArray();

        return ['vendor' => $translations];
    }

    protected function getPhpTranslations(string $folder) : array
    {
        $files = collect($this->filesystem->files($folder));

        $excludedFiles = collect(config('poeditor-sync.excluded_files', []))
            ->map(function ($excludedFile) {
                if (Str::endsWith($excludedFile, '.php')) {
                    return $excludedFile;
                }

                return "{$excludedFile}.php";
            });

        return $files
            ->reject(function ($file) use ($excludedFiles) {
                return $excludedFiles->contains($file->getFilename());
            })->mapWithKeys(function ($file) {
                $filename = pathinfo($file->getRealPath(), PATHINFO_FILENAME);

                return [$filename => $this->filesystem->getRequire($file->getRealPath())];
            })
            ->toArray();
    }

    protected function getJsonTranslations(string $filename) : array
    {
        if (! $this->filesystem->exists($filename)) {
            return [];
        }

        return json_decode($this->filesystem->get($filename), true);
    }

    protected function createPhpTranslationFiles(array $translations, string $locale) : void
    {
        $this->createPhpFiles($this->getLangPath($locale), $translations);
    }

    protected function createJsonTranslationFile(array $translations, string $locale) : void
    {
        $this->createJsonFile($this->getLangPath("/{$locale}.json"), $translations);
    }

    protected function createVendorTranslationFiles(array $translations, string $locale) : void
    {
        if (! Arr::has($translations, 'vendor')) {
            return;
        }

        foreach ($translations['vendor'] as $package => $packageTranslations) {
            $path = $this->getLangPath("/vendor/{$package}/{$locale}");

            if (! $this->filesystem->exists($path)) {
                $this->filesystem->makeDirectory($path, 0755, true);
            } else {
                $this->filesystem->cleanDirectory($path);
            }

            $this->createPhpFiles($path, $packageTranslations);
            $this->createJsonFile("{$path}.json", $packageTranslations);
        }
    }

    protected function createPhpFiles(string $folder, array $translations) : void
    {
        $translations = Arr::where($translations, function ($translation) {
            return is_array($translation);
        });

        foreach ($translations as $filename => $fileTranslations) {
            $array = VarExporter::export($fileTranslations);

            if ($filename === 'vendor') {
                continue;
            }

            $this->filesystem->put(
                "{$folder}/{$filename}.php",
                '<?php' . PHP_EOL . PHP_EOL . "return {$array};" . PHP_EOL,
            );
        }
    }

    protected function createJsonFile(string $filename, array $translations) : void
    {
        $translations = Arr::where($translations, function ($translation) {
            return is_string($translation);
        });

        if (empty($translations)) {
            return;
        }

        $this->filesystem->put($filename, json_encode($translations, JSON_PRETTY_PRINT));
    }

    protected function createEmptyLocaleFolder(string $locale) : void
    {
        if (! $this->filesystem->exists($this->getLangPath())) {
            $this->filesystem->makeDirectory($this->getLangPath());
        }

        $path = $this->getLangPath($locale);

        if (file_exists($path)) {
            foreach ($this->filesystem->allFiles($path) as $file) {
                if (! in_array($file->getFilename(), config('poeditor-sync.excluded_files', []))) {
                    $this->filesystem->delete($file->getRealPath());
                }
            }

            return;
        }

        $this->filesystem->makeDirectory($path);
    }

    protected function getLangPath(string $path = null) : string
    {
        if (function_exists('lang_path')) {
            return lang_path($path);
        }

        return resource_path("lang/{$path}");
    }
}
