<?php

namespace NextApps\PoeditorSync\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;

class HasMatchingPluralization implements ValidationRule
{
    public function validate(string $translationKey, mixed $translations, Closure $fail) : void
    {
        $expectedPluralizationParts = $this->getPluralizationParts($translations[$this->getFallbackLocale()]);

        collect($translations)
            ->except($this->getFallbackLocale())
            ->each(function (string $text, string $locale) use ($expectedPluralizationParts, $fail) {
                $pluralizationParts = $this->getPluralizationParts($text);

                if ($expectedPluralizationParts->isNotEmpty() && $pluralizationParts->isEmpty()) {
                    $fail("Missing pluralization in locale '{$locale}'");
                } elseif ($expectedPluralizationParts->isEmpty() && $pluralizationParts->isNotEmpty()) {
                    $fail("Unexpected pluralization in locale '{$locale}'");
                } elseif ($pluralizationParts->all() !== $expectedPluralizationParts->all()) {
                    $fail("Invalid pluralization in locale '{$locale}'");
                }
            });
    }

    protected function getPluralizationParts(string $text) : Collection
    {
        preg_match_all('/({\d*}*)|(\|)|(\[\d*,(?:\d+|\**)\])/', $text, $matches);

        return collect($matches[0]);
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
