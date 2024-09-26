<?php

namespace Wotz\PoeditorSync\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HasMatchingReplacements implements ValidationRule
{
    public function validate(string $translationKey, mixed $translations, Closure $fail) : void
    {
        $expectedReplacements = $this->getReplacements($translations[$this->getFallbackLocale()]);

        collect($translations)
            ->except($this->getFallbackLocale())
            ->each(function (string $text, string $locale) use ($expectedReplacements, $fail) {
                $missingReplacements = $expectedReplacements->diff($replacements = $this->getReplacements($text));
                $unexpectedReplacements = $replacements->diff($expectedReplacements);

                $missingReplacements->each(function ($missingReplacement) use ($fail, $locale) {
                    $fail("Missing replacement key '{$missingReplacement}' in {$locale}");
                });

                $unexpectedReplacements->each(function ($unexpectedReplacement) use ($fail, $locale) {
                    $fail("Unexpected replacement key '{$unexpectedReplacement}' in {$locale}");
                });
            });
    }

    protected function getReplacements(string $text) : Collection
    {
        return Str::matchAll('/:[a-zA-Z][a-zA-Z0-9]*/', $text)->map(fn ($replacement) => Str::lower($replacement));
    }

    protected function getFallbackLocale() : string
    {
        return config('app.fallback_locale');
    }

    protected function getLocales() : Collection
    {
        return collect(config('poeditor-sync.locales'));
    }
}
