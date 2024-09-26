<?php

namespace Wotz\PoeditorSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Wotz\PoeditorSync\Translations\TranslationManager;
use Wotz\PoeditorSync\Validation\HasMatchingPluralization;
use Wotz\PoeditorSync\Validation\HasMatchingReplacements;

class ValidateCommand extends Command
{
    protected $signature = 'poeditor:validate';

    protected $description = 'Validate that translations have the same replacements and correct pluralization';

    public function handle() : int
    {
        $translationsPerLocale = $this->getLocales()->mapWithKeys(function ($locale) {
            return [$locale => collect(app(TranslationManager::class)->getTranslations($locale))->dot()];
        });

        $validator = Validator::make(
            $translationsPerLocale[config('app.fallback_locale')]
                ->keys()
                ->mapWithKeys(function ($key) use ($translationsPerLocale) {
                    return [$key => $translationsPerLocale->map(fn ($translations) => $translations->get($key))];
                })
                ->map(fn ($translations) => $translations->filter()->all())
                ->filter(fn ($translations) => count($translations) > 0)
                ->all(),
            [
                '*' => [
                    'array:' . $this->getLocales()->implode(','),
                    new HasMatchingReplacements(),
                    new HasMatchingPluralization(),
                ],
            ]
        );

        if ($validator->passes()) {
            $this->info('All translations are valid!');

            return Command::SUCCESS;
        }

        $this->table(
            ['Translation Key', 'Errors'],
            collect($validator->messages()->keys())->map(fn (string $key) => [
                $key,
                collect($validator->messages()->get($key))->map(fn ($message) => "- {$message}")->join(PHP_EOL),
            ])->all()
        );

        return Command::FAILURE;
    }

    protected function getLocales() : Collection
    {
        return collect(config('poeditor-sync.locales'))->map(fn ($locales) => Arr::wrap($locales)[0]);
    }
}
