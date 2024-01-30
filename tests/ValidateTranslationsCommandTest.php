<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\VarExporter\VarExporter;

class ValidateTranslationsCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app(Filesystem::class)->cleanDirectory(lang_path());
        app(Filesystem::class)->makeDirectory(lang_path('en'));
        app(Filesystem::class)->makeDirectory(lang_path('fr'));

        config(['app.supported_locales' => ['en', 'nl', 'fr']]);
    }

    // /** @test */
    // public function it_counts_total_string_variables_per_language()
    // {
    //     $this->createPhpTranslationFile(lang_path('en/php-file.php'), ['foo' => ':bar']);
    //     $this->createPhpTranslationFile(lang_path('nl/php-file.php'), ['foo' => ':bar']);
    //     $this->createPhpTranslationFile(lang_path('fr/php-file.php'), ['foo' => ':bar']);

    //     $this->artisan('poeditor:validate')
    //         ->expectsOutput('The following amount of string variables were found per language:')
    //         ->expectsTable(
    //             ['Language', 'String variables'],
    //             [
    //                 ['en', 1],
    //                 ['nl', 1],
    //                 ['fr', 1],
    //             ]
    //         )
    //         ->assertExitCode(0);
    // }

    // /** @test */
    // public function it_shows_extra_or_missing_string_attributes()
    // {
    //     $this->createPhpTranslationFile(lang_path('en/php-file.php'), ['foo' => ':bar']);
    //     $this->createPhpTranslationFile(lang_path('nl/php-file.php'), ['foo' => 'bar']);
    //     $this->createPhpTranslationFile(lang_path('fr/php-file.php'), ['foo' => ':bar']);

    //     $this->artisan('poeditor:validate')
    //         ->expectsOutput('It seems there are some string variables that are not available in other languages.')
    //         ->expectsOutput('There might be something wrong with the string variables for the following translation keys:')
    //         ->expectsTable(
    //             ['Extra string variables'],
    //             [
    //                 ['php-file.foo.:bar'],
    //             ]
    //         )
    //         ->assertExitCode(0);
    // }

    /** @test */
    public function it_show_invalid_string_variables()
    {
        $this->createPhpTranslationFile(lang_path('en/php-file.php'), ['foo' => ':bar']);
        $this->createPhpTranslationFile(lang_path('nl/php-file.php'), ['foo' => ':baz']);
        $this->createPhpTranslationFile(lang_path('fr/php-file.php'), ['foo' => ':baz']);

        $this->createPhpTranslationFile(lang_path('en/other-file.php'), ['bar' => 'foo bar :baz']);
        $this->createPhpTranslationFile(lang_path('nl/other-file.php'), ['bar' => 'foo bar :baz']);
        $this->createPhpTranslationFile(lang_path('fr/other-file.php'), ['bar' => 'foo :bar baz']);

        $this->artisan('poeditor:validate')
            ->expectsOutput('It seems there are some translations that could be invalid in some languages.')
            ->expectsTable(
                ['Language', 'Translation key', 'Original', 'Translated', 'Missing'],
                [
                    ['nl', 'php-file.foo', ':bar', ':baz', ':bar'],
                    ['fr', 'other-file.bar', 'foo bar :baz', 'foo :bar baz', ':baz'],
                    ['fr', 'php-file.foo', ':bar', ':baz', ':bar'],
                ]
            )
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_invalid_pluralization_strings()
    {
        $this->createPhpTranslationFile(lang_path('en/php-file.php'), ['foo' => '{0} bar|{1} baz|[2,*] baz baz']);
        $this->createPhpTranslationFile(lang_path('nl/php-file.php'), ['foo' => '{0} bar|{1} baz']);
        $this->createPhpTranslationFile(lang_path('fr/php-file.php'), ['foo' => '{0} bar|{1} baz|[2,*] baz baz']);

        $this->artisan('poeditor:validate')
            ->expectsOutput('There might be something wrong with the pluralization for the following translation key:')
            ->expectsTable(
                ['Translation key'],
                [
                    ['php-file.foo'],
                ]
            )
            ->assertExitCode(0);
    }

    public function createPhpTranslationFile(string $filename, array $data) : void
    {
        if (! app(Filesystem::class)->exists(dirname($filename))) {
            app(Filesystem::class)->makeDirectory(dirname($filename), 0755, true);
        }

        file_put_contents(
            $filename,
            '<?php' . PHP_EOL . PHP_EOL . 'return ' . VarExporter::export($data) . ';' . PHP_EOL
        );
    }

    public function createJsonTranslationFile(string $filename, array $data) : void
    {
        if (! app(Filesystem::class)->exists(dirname($filename))) {
            app(Filesystem::class)->makeDirectory(dirname($filename), 0755, true);
        }

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}
