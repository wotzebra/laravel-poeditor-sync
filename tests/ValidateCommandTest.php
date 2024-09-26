<?php

namespace Wotz\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;

class ValidateCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app(Filesystem::class)->cleanDirectory(lang_path());
        app(Filesystem::class)->makeDirectory(lang_path('en'));

        config()->set('app.fallback_locale', 'en');
        config()->set('poeditor-sync.locales', ['en', 'nl-be' => ['nl', 'nl_NL'], 'fr']);
    }

    /** @test */
    public function it_validates_translations()
    {
        $this->createPhpTranslationFile('en/php-file.php', ['foo' => 'bar :foo en']);
        $this->createJsonTranslationFile('en.json', ['bar' => 'foo singular en|foo plural en']);

        $this->createPhpTranslationFile('nl/php-file.php', ['foo' => 'bar :bar nl']);
        $this->createJsonTranslationFile('nl.json', ['bar' => 'foo singular nl|foo plural nl']);

        $this->createPhpTranslationFile('fr/php-file.php', ['foo' => 'bar :foo nl']);
        $this->createJsonTranslationFile('fr.json', ['bar' => 'foo nl']);

        $this->artisan('poeditor:validate')
            ->expectsTable(
                [
                    'Translation Key',
                    'Errors',
                ],
                [
                    [
                        'php-file.foo',
                        '- Missing replacement key \':foo\' in nl' . PHP_EOL . '- Unexpected replacement key \':bar\' in nl',
                    ],
                    [
                        'bar',
                        '- Missing pluralization in locale \'fr\'',
                    ],
                ]
            )
            ->assertExitCode(1);

        $this->createPhpTranslationFile('nl/php-file.php', ['foo' => 'bar :foo nl']);
        $this->createJsonTranslationFile('fr.json', ['bar' => 'foo singular fr|foo plural fr']);

        $this->artisan('poeditor:validate')
            ->expectsOutput('All translations are valid!')
            ->assertExitCode(0);
    }
}
