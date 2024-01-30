<?php

namespace NextApps\PoeditorSync\Translations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\VarExporter\VarExporter;

class TranslationManager
{
    public function __construct(
        protected Filesystem $filesystem
    ) {
    }

    public function getTranslations(string $locale) : array
    {
        return $this->getPhpTranslations(lang_path("/{$locale}"))
            ->merge($this->getJsonTranslations(lang_path("/{$locale}.json")))
            ->when($this->includeVendorTranslations(), function ($translations) use ($locale) {
                return $translations->merge($this->getVendorTranslations($locale));
            })
            ->jsonSerialize();
    }

    public function createTranslationFiles(Collection $translations, string $locale) : void
    {
        $this->createEmptyLocaleFolder($locale);

        $this->createPhpTranslationFiles($translations, $locale);
        $this->createJsonTranslationFile($translations, $locale);

        if ($this->includeVendorTranslations()) {
            $this->createVendorTranslationFiles($translations, $locale);
        }
    }

    public function getPossibleInvalidTranslations() : Collection
    {
        $fallbackLocale = config('app.fallback_locale');
        $appLanguages = collect(config('app.supported_locales'))->reject(fn ($locale) => $locale === $fallbackLocale);

        return $appLanguages->map(function ($locale) use ($fallbackLocale) {
            $toBecheckedTranslations = collect($this->getTranslations($locale))->dot();

            return collect($this->getTranslations($fallbackLocale))->dot()->filter(
                function (string $translation, string $key) use ($toBecheckedTranslations) {
                    $toBecheckedTranslation = $toBecheckedTranslations->get($key);

                    return empty($toBecheckedTranslation)
                        || (
                            ($replacements = $this->getReplacementKeys($translation))
                            && $this->getMissingReplacementKeys(
                                $this->getReplacementKeys($toBecheckedTranslation),
                                $replacements
                            )->isNotEmpty()
                        );
                }
            )->map(function (string $translation, string $key) use ($toBecheckedTranslations, $locale) {
                return [
                    'key' => $key,
                    'locale' => $locale,
                    'original' => $translation,
                    'translated' => $translated = $toBecheckedTranslations->get($key),
                    'missing' => $this->getMissingReplacementKeys(
                        $this->getReplacementKeys($translated),
                        $this->getReplacementKeys($translation)
                    ),
                ];
            });
        })->flatten(1);
    }

    public function checkPluralization() : Collection
    {
        $appLanguages = collect(config('app.supported_locales'));

        $stringVariables = [];

        $appLanguages->each(function ($locale) use (&$stringVariables) {
            collect($this->getTranslations($locale))
                ->dot()
                ->mapWithKeys(function ($translation, $key) {
                    $matched = preg_match_all('/({\d*}*)|(\|)|(\[\d*,(?:\d+|\**)\])/', $translation, $matches);

                    return [$key => $matched ? $matches[0] : []];
                })
                ->each(function ($matches, $key) use (&$stringVariables, $locale) {
                    if (! isset($stringVariables[$key])) {
                        $stringVariables[$key] = [];
                    }

                    $stringVariables[$key][$locale] = $matches;
                });
        });

        return collect($stringVariables)
            ->reject(function ($matches) use ($appLanguages) {
                if (collect($matches)->keys()->toArray() !== $appLanguages->toArray()) {
                    return false;
                }

                return collect($matches)->unique()->count() === 1;
            })->keys();
    }

    public function countStringVariables(string $locale) : int
    {
        return collect($this->getTranslations($locale))->dot()->sum(function (string $translation) {
            return $this->getReplacementKeys($translation)->count();
        });
    }

    public function getExtraStringVariables() : Collection
    {
        $appLanguages = collect(config('app.supported_locales'));

        $stringVariables = [];

        $appLanguages->each(function ($locale) use (&$stringVariables) {
            collect($this->getTranslations($locale))->dot()->filter(function ($translation) {
                return $this->getReplacementKeys($translation)->isNotEmpty();
            })->map(function ($translation, $key) {
                return $this->getReplacementKeys($translation)->map(function ($replacementKey) use ($key) {
                    return $key . '.' . Str::lower($replacementKey);
                });
            })
                ->flatten()
                ->each(function ($translation) use (&$stringVariables, $locale) {
                    if (! isset($stringVariables[$translation])) {
                        $stringVariables[$translation] = [];
                    }

                    $stringVariables[$translation][] = $locale;
                });
        });

        return collect($stringVariables)->reject(function ($locales) use ($appLanguages) {
            return $locales === $appLanguages->toArray();
        })->keys();
    }

    protected function getReplacementKeys(string $translation) : Collection
    {
        return Str::matchAll('/:[a-zA-Z][a-zA-Z0-9]*/', $translation);
    }

    protected function getMissingReplacementKeys(
        Collection $toBeCheckedReplacementKeys,
        Collection $replacementKeys
    ) : Collection {
        return $replacementKeys->reject(function ($replacementKey) use ($toBeCheckedReplacementKeys) {
            return $toBeCheckedReplacementKeys->contains(function ($val) use ($replacementKey) {
                return Str::lower($val) === Str::lower($replacementKey);
            });
        });
    }

    protected function getVendorTranslations(string $locale) : Collection
    {
        if (! $this->filesystem->exists(lang_path('vendor'))) {
            return collect();
        }

        $directories = collect($this->filesystem->directories(lang_path('vendor')));

        $translations = $directories->mapWithKeys(function ($directory) use ($locale) {
            $phpTranslations = $this->getPhpTranslations("{$directory}/{$locale}");
            $jsonTranslations = $this->getJsonTranslations("{$directory}/{$locale}.json");

            return [basename($directory) => $phpTranslations->merge($jsonTranslations)];
        });

        return collect(['vendor' => $translations]);
    }

    protected function getPhpTranslations(string $folder) : Collection
    {
        return collect($this->filesystem->files($folder))
            ->reject(fn ($file) => $this->getExcludedFilenames()->contains($file->getFilename()))
            ->mapWithKeys(function ($file) {
                $filename = pathinfo($file->getRealPath(), PATHINFO_FILENAME);

                return [$filename => $this->filesystem->getRequire($file->getRealPath())];
            });
    }

    protected function getJsonTranslations(string $filename) : array
    {
        if (! $this->filesystem->exists($filename)) {
            return [];
        }

        return json_decode($this->filesystem->get($filename), true);
    }

    protected function createPhpTranslationFiles(Collection $translations, string $locale) : void
    {
        $this->createPhpFiles(lang_path($locale), $translations);
    }

    protected function createJsonTranslationFile(Collection $translations, string $locale) : void
    {
        $this->createJsonFile(lang_path("/{$locale}.json"), $translations);
    }

    protected function createVendorTranslationFiles(Collection $translations, string $locale) : void
    {
        collect($translations['vendor'] ?? [])->each(function ($packageTranslations, $package) use ($locale) {
            $path = lang_path("/vendor/{$package}/{$locale}");

            $this->filesystem->ensureDirectoryExists($path);
            $this->filesystem->cleanDirectory($path);

            $this->createPhpFiles($path, collect($packageTranslations));
            $this->createJsonFile("{$path}.json", collect($packageTranslations));
        });
    }

    protected function createPhpFiles(string $folder, Collection $translations) : void
    {
        $translations->filter(function ($translation) {
            return is_array($translation);
        })->each(function ($fileTranslations, $filename) use ($folder) {
            if ($filename === 'vendor') {
                return;
            }

            $this->filesystem->put(
                "{$folder}/{$filename}.php",
                '<?php' . PHP_EOL . PHP_EOL . 'return ' . VarExporter::export($fileTranslations) . ';' . PHP_EOL,
            );
        });
    }

    protected function createJsonFile(string $filename, Collection $translations) : void
    {
        $translations->filter(function ($translation) {
            return is_string($translation);
        })->whenNotEmpty(function ($translations) use ($filename) {
            $this->filesystem->put($filename, json_encode($translations, JSON_PRETTY_PRINT));
        });
    }

    protected function createEmptyLocaleFolder(string $locale) : void
    {
        $this->filesystem->ensureDirectoryExists(lang_path());
        $this->filesystem->ensureDirectoryExists($path = lang_path($locale));

        collect($this->filesystem->allFiles($path))
            ->filter(fn ($file) => $this->getExcludedFilenames()->doesntContain($file->getFilename()))
            ->each(fn ($file) => $this->filesystem->delete($file->getRealPath()));
    }

    protected function getExcludedFilenames() : Collection
    {
        return collect(config('poeditor-sync.excluded_files', []))->map(function ($excludedFile) {
            if (Str::endsWith($excludedFile, '.php')) {
                return $excludedFile;
            }

            return "{$excludedFile}.php";
        });
    }

    protected function includeVendorTranslations() : bool
    {
        return config('poeditor-sync.include_vendor');
    }
}
