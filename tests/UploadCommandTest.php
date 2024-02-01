<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Poeditor\UploadResponse;

class UploadCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app(Filesystem::class)->cleanDirectory(lang_path());
        app(Filesystem::class)->makeDirectory(lang_path('en'));
    }

    /** @test */
    public function it_uploads_php_translations_of_default_locale()
    {
        $this->createPhpTranslationFile('en/first-en-php-file.php', ['foo' => 'bar']);
        $this->createPhpTranslationFile('en/second-en-php-file.php', ['bar' => 'foo']);

        $this->createPhpTranslationFile('nl/nl-php-file.php', ['foo_bar' => 'bar foo']);

        $this->mockPoeditorUpload('en', [
            'first-en-php-file' => [
                'foo' => 'bar',
            ],
            'second-en-php-file' => [
                'bar' => 'foo',
            ],
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_json_translations_of_default_locale()
    {
        $this->createJsonTranslationFile('en.json', ['foo' => 'bar', 'foo_bar' => 'bar foo']);

        $this->createJsonTranslationFile('nl.json', ['bar' => 'foo']);

        $this->mockPoeditorUpload('en', [
            'foo' => 'bar',
            'foo_bar' => 'bar foo',
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_vendor_translations()
    {
        $this->createPhpTranslationFile('vendor/first-package/en/first-package-php-file.php', ['bar_foo_bar' => 'foo bar foo']);
        $this->createJsonTranslationFile('vendor/first-package/en.json', ['bar_foo' => 'foo bar']);

        $this->createPhpTranslationFile('vendor/second-package/en/second-package-php-file.php', ['foo_bar_foo' => 'bar foo bar']);
        $this->createJsonTranslationFile('vendor/second-package/en.json', ['foo_bar' => 'bar foo']);

        $this->mockPoeditorUpload('en', [
            'vendor' => [
                'first-package' => [
                    'first-package-php-file' => [
                        'bar_foo_bar' => 'foo bar foo',
                    ],
                    'bar_foo' => 'foo bar',
                ],
                'second-package' => [
                    'second-package-php-file' => [
                        'foo_bar_foo' => 'bar foo bar',
                    ],
                    'foo_bar' => 'bar foo',
                ],
            ],
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_php_and_json_and_vendor_translations()
    {
        $this->createPhpTranslationFile('en/php-file.php', ['bar' => 'foo']);
        $this->createJsonTranslationFile('en.json', ['foo_bar' => 'bar foo']);
        $this->createPhpTranslationFile('vendor/package-name/en/package-php-file.php', ['bar_foo_bar' => 'foo bar foo']);
        $this->createJsonTranslationFile('vendor/package-name/en.json', ['bar_foo' => 'foo bar']);

        $this->mockPoeditorUpload('en', [
            'php-file' => [
                'bar' => 'foo',
            ],
            'foo_bar' => 'bar foo',
            'vendor' => [
                'package-name' => [
                    'package-php-file' => [
                        'bar_foo_bar' => 'foo bar foo',
                    ],
                    'bar_foo' => 'foo bar',
                ],
            ],
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_translations_of_provided_locale()
    {
        $this->createPhpTranslationFile('en/en-php-file.php', ['bar' => 'foo']);
        $this->createJsonTranslationFile('en.json', ['foo_bar' => 'bar foo']);

        $this->createPhpTranslationFile('nl/nl-php-file.php', ['foo' => 'bar']);
        $this->createJsonTranslationFile('nl.json', ['bar_foo' => 'foo bar']);

        $this->mockPoeditorUpload('nl', [
            'nl-php-file' => [
                'foo' => 'bar',
            ],
            'bar_foo' => 'foo bar',
        ]);

        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->artisan('poeditor:upload nl')->assertExitCode(0);
    }

    /** @test */
    public function it_outputs_upload_response_info()
    {
        $this->mockPoeditorUpload('en', response: $response = $this->getPoeditorUploadResponse());

        $this->artisan('poeditor:upload')
            ->expectsOutput('All translations have been uploaded:')
            ->expectsOutput("{$response->getAddedTermsCount()} terms added")
            ->expectsOutput("{$response->getDeletedTermsCount()} terms deleted")
            ->expectsOutput("{$response->getAddedTranslationsCount()} translations added")
            ->expectsOutput("{$response->getUpdatedTranslationsCount()} translations updated")
            ->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_with_overwrite_enabled()
    {
        $this->mockPoeditorUpload('en', [], true);

        $this->artisan('poeditor:upload --force')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_does_not_upload_vendor_translations_if_disabled_in_config()
    {
        config()->set('poeditor-sync.include_vendor', false);

        $this->createPhpTranslationFile('en/php-file.php', ['bar' => 'foo']);
        $this->createJsonTranslationFile('en.json', ['foo_bar' => 'bar foo']);

        $this->createPhpTranslationFile('vendor/package-name/en/php-file.php', ['bar_foo_bar' => 'foo bar foo']);
        $this->createJsonTranslationFile('vendor/package-name/en.json', ['bar_foo' => 'foo bar']);

        $this->mockPoeditorUpload('en', [
            'php-file' => [
                'bar' => 'foo',
            ],
            'foo_bar' => 'bar foo',
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_does_not_upload_translation_files_that_have_been_excluded_in_config()
    {
        config()->set('poeditor-sync.excluded_files', [
            'auth',
            'validation.php',
        ]);

        $this->createPhpTranslationFile('en/php-file.php', ['bar' => 'foo']);
        $this->createJsonTranslationFile('en.json', ['foo_bar' => 'bar foo']);

        $this->createPhpTranslationFile('en/auth.php', ['bar' => 'foo']);
        $this->createPhpTranslationFile('en/validation.php', ['foobar' => 'barfoo']);

        $this->mockPoeditorUpload('en', [
            'php-file' => [
                'bar' => 'foo',
            ],
            'foo_bar' => 'bar foo',
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_maps_internal_locale_on_poeditor_locale()
    {
        config()->set('poeditor-sync.locales', ['en-gb' => 'en']);

        $this->createPhpTranslationFile('en/en-php-file.php', ['bar' => 'foo']);
        $this->createJsonTranslationFile('en.json', ['foo_bar' => 'bar foo']);

        $this->mockPoeditorUpload('en-gb', [
            'en-php-file' => [
                'bar' => 'foo',
            ],
            'foo_bar' => 'bar foo',
        ]);

        $this->artisan('poeditor:upload en')->assertExitCode(0);
    }

    /** @test */
    public function it_maps_one_of_multiple_internal_locale_on_the_poeditor_locale()
    {
        config()->set('poeditor-sync.locales', ['nl' => ['nl_BE', 'nl_NL']]);

        $this->createPhpTranslationFile('nl_NL/nl-php-file.php', ['bar' => 'foo NL']);
        $this->createJsonTranslationFile('nl_NL.json', ['foo_bar' => 'bar foo NL']);

        $this->createPhpTranslationFile('nl_BE/nl-php-file.php', ['bar' => 'foo BE']);
        $this->createJsonTranslationFile('nl_BE.json', ['foo_bar' => 'bar foo BE']);

        $this->mockPoeditorUpload('nl', [
            'nl-php-file' => [
                'bar' => 'foo NL',
            ],
            'foo_bar' => 'bar foo NL',
        ]);

        $this->artisan('poeditor:upload nl_NL')->assertExitCode(0);

        $this->mockPoeditorUpload('nl', [
            'nl-php-file' => [
                'bar' => 'foo BE',
            ],
            'foo_bar' => 'bar foo BE',
        ]);

        $this->artisan('poeditor:upload nl_BE')->assertExitCode(0);
    }

    /** @test */
    public function it_throws_error_if_provided_locale_is_not_present_in_config_locales_array()
    {
        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->mockPoeditorUpload('fr', []);

        $this->artisan('poeditor:upload fr')
            ->assertExitCode(1)
            ->expectsOutput('Invalid locale provided!');
    }

    /** @test */
    public function it_throws_error_if_default_locale_is_not_present_in_config_locales_array()
    {
        app()->setLocale('fr');

        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->mockPoeditorUpload('fr', []);

        $this->artisan('poeditor:upload')
            ->assertExitCode(1)
            ->expectsOutput('Invalid locale provided!');
    }

    public function mockPoeditorUpload(string $language, array $translations = [], bool $overwrite = false, UploadResponse $response = null)
    {
        $this->mock(Poeditor::class)
            ->shouldReceive('upload')
            ->with($language, $translations, $overwrite)
            ->andReturn($response ?? $this->getPoeditorUploadResponse());
    }

    public function getPoeditorUploadResponse() : UploadResponse
    {
        return new UploadResponse([
            'result' => [
                'terms' => ['added' => $this->faker->randomNumber(), 'deleted' => $this->faker->randomNumber()],
                'translations' => ['added' => $this->faker->randomNumber(), 'updated' => $this->faker->randomNumber()],
            ],
        ]);
    }
}
