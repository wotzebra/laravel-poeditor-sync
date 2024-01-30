<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use NextApps\PoeditorSync\Translations\TranslationManager;

class ValidateTranslationsCommand extends Command
{
    protected $signature = 'poeditor:validate';

    protected $description = 'Validate that translations have the same parameters and pluralization';

    public function handle() : int
    {
        $stringVariables = collect(config('app.supported_locales'))->map(
            fn ($language) => [
                $language,
                app(TranslationManager::class)->countStringVariables($language)
            ]
        );

        $this->info('The following amount of string variables were found per language:');
        $this->table(
            ['Language', 'String variables'],
            $stringVariables->toArray()
        );

        if($stringVariables->max(fn ($item) => $item[1]) !== $stringVariables->min(fn ($item) => $item[1])) {
            $this->info('It seems there are some string variables that are not available in other languages.');

            $extraStringVariables = app(TranslationManager::class)->getExtraStringVariables();

            if($extraStringVariables->isNotEmpty()) {
                $this->info('There might be something wrong with the string variables for the following translation keys:');
                $this->table(
                    ['Extra string variables'],
                    $extraStringVariables->map(fn ($item) => [$item])->toArray()
                );
            }
        }

        $invalidTranslations = app(TranslationManager::class)->getPossibleInvalidTranslations();

        if($invalidTranslations->isNotEmpty()) {
            $this->info('It seems there are some translations that could be invalid in some languages.');
            $this->table(
                ['Language', 'Translation key', 'Original', 'Translated', 'Missing'],
                $invalidTranslations->map(
                    fn ($item, $key) => [
                        $item['locale'],
                        $item['key'],
                        $item['original'],
                        $item['translated'],
                        $item['missing']->implode(', ')
                    ]
                )->toArray()
            );
        }

        $pluralizationErrors = app(TranslationManager::class)->checkPluralization();

        if($pluralizationErrors->isNotEmpty()) {
            $this->info('There might be something wrong with the pluralization for the following translation key:');
            $this->table(
                ['Translation key'],
                $pluralizationErrors->map(
                    fn ($item, $key) => [
                        $item,
                    ]
                )->toArray()
            );
        }

        $this->info('All checks have been successfully completed.');

        return Command::SUCCESS;
    }
}
