<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Poeditor\UploadResponse;
use Symfony\Component\VarExporter\VarExporter;

class UploadCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app(Filesystem::class)->cleanDirectory($this->getLangPath());
        app(Filesystem::class)->makeDirectory($this->getLangPath('en'));
    }

    /** @test */
    public function it_uploads_php_translations_of_default_locale()
    {
        $this->createPhpTranslationFile($this->getLangPath('en/first-en-php-file.php'), ['foo' => 'bar']);
        $this->createPhpTranslationFile($this->getLangPath('en/second-en-php-file.php'), ['bar' => 'foo']);

        $this->createPhpTranslationFile($this->getLangPath('nl/nl-php-file.php'), ['foo_bar' => 'bar foo']);

        $this->mockPoeditorUpload('en', [
            'first-en-php-file' => [
                'foo' => 'bar',
            ],
            'second-en-php-file' => [
                'bar' => 'foo',
            ],
        ]);

        $this->mock(Poeditor::class, function ($mock) {
            $mock->shouldReceive('upload')
                ->with(
                    'en',
                    [
                        'first-en-php-file' => [
                            'foo' => 'bar',
                        ],
                        'second-en-php-file' => [
                            'bar' => 'foo',
                        ],
                    ],
                    false
                )
                ->andReturn($this->getUploadResponse());
        });

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_json_translations_of_default_locale()
    {
        $this->createJsonTranslationFile($this->getLangPath('en.json'), ['foo' => 'bar', 'foo_bar' => 'bar foo']);

        $this->createJsonTranslationFile($this->getLangPath('nl.json'), ['bar' => 'foo']);

        $this->mockPoeditorUpload('en', [
            'foo' => 'bar',
            'foo_bar' => 'bar foo',
        ]);

        $this->artisan('poeditor:upload')->assertExitCode(0);
    }

    /** @test */
    public function it_uploads_vendor_translations()
    {
        $this->createPhpTranslationFile($this->getLangPath('vendor/first-package/en/first-package-php-file.php'), ['bar_foo_bar' => 'foo bar foo']);
        $this->createJsonTranslationFile($this->getLangPath('vendor/first-package/en.json'), ['bar_foo' => 'foo bar']);

        $this->createPhpTranslationFile($this->getLangPath('vendor/second-package/en/second-package-php-file.php'), ['foo_bar_foo' => 'bar foo bar']);
        $this->createJsonTranslationFile($this->getLangPath('vendor/second-package/en.json'), ['foo_bar' => 'bar foo']);

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
        $this->createPhpTranslationFile($this->getLangPath('en/php-file.php'), ['bar' => 'foo']);
        $this->createJsonTranslationFile($this->getLangPath('en.json'), ['foo_bar' => 'bar foo']);
        $this->createPhpTranslationFile($this->getLangPath('vendor/package-name/en/package-php-file.php'), ['bar_foo_bar' => 'foo bar foo']);
        $this->createJsonTranslationFile($this->getLangPath('vendor/package-name/en.json'), ['bar_foo' => 'foo bar']);

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
        $this->createPhpTranslationFile($this->getLangPath('en/en-php-file.php'), ['bar' => 'foo']);
        $this->createJsonTranslationFile($this->getLangPath('en.json'), ['foo_bar' => 'bar foo']);

        $this->createPhpTranslationFile($this->getLangPath('nl/nl-php-file.php'), ['foo' => 'bar']);
        $this->createJsonTranslationFile($this->getLangPath('nl.json'), ['bar_foo' => 'foo bar']);

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
        $this->mockPoeditorUpload('en', [], false, $response = $this->getUploadResponse());

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

        $this->createPhpTranslationFile($this->getLangPath('en/php-file.php'), ['bar' => 'foo']);
        $this->createJsonTranslationFile($this->getLangPath('en.json'), ['foo_bar' => 'bar foo']);

        $this->createPhpTranslationFile($this->getLangPath('vendor/package-name/en/php-file.php'), ['bar_foo_bar' => 'foo bar foo']);
        $this->createJsonTranslationFile($this->getLangPath('vendor/package-name/en.json'), ['bar_foo' => 'foo bar']);

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

        $this->createPhpTranslationFile($this->getLangPath('en/php-file.php'), ['bar' => 'foo']);
        $this->createJsonTranslationFile($this->getLangPath('en.json'), ['foo_bar' => 'bar foo']);

        $this->createPhpTranslationFile($this->getLangPath('en/auth.php'), ['bar' => 'foo']);
        $this->createPhpTranslationFile($this->getLangPath('en/validation.php'), ['foobar' => 'barfoo']);

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

        $this->createPhpTranslationFile($this->getLangPath('en/en-php-file.php'), ['bar' => 'foo']);
        $this->createJsonTranslationFile($this->getLangPath('en.json'), ['foo_bar' => 'bar foo']);

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

        $this->createPhpTranslationFile($this->getLangPath('nl_NL/nl-php-file.php'), ['bar' => 'foo NL']);
        $this->createJsonTranslationFile($this->getLangPath('nl_NL.json'), ['foo_bar' => 'bar foo NL']);

        $this->createPhpTranslationFile($this->getLangPath('nl_BE/nl-php-file.php'), ['bar' => 'foo BE']);
        $this->createJsonTranslationFile($this->getLangPath('nl_BE.json'), ['foo_bar' => 'bar foo BE']);

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

    public function mockPoeditorUpload(string $language, array $translations, bool $overwrite = false, UploadResponse $response = null)
    {
        $this->mock(Poeditor::class, function ($mock) use ($language, $translations, $overwrite, $response) {
            $mock->shouldReceive('upload')
                ->with($language, $translations, $overwrite)
                ->andReturn($response ?? $this->getUploadResponse());
        });
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

    public function getUploadResponse() : UploadResponse
    {
        return new UploadResponse([
            'result' => [
                'terms' => ['added' => $this->faker->randomNumber(), 'deleted' => $this->faker->randomNumber()],
                'translations' => ['added' => $this->faker->randomNumber(), 'updated' => $this->faker->randomNumber()],
            ],
        ]);
    }
}
