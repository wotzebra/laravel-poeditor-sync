<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use NextApps\PoeditorSync\Poeditor\Poeditor;

class DownloadCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app(Filesystem::class)->cleanDirectory(lang_path());
        app(Filesystem::class)->makeDirectory(lang_path('en'));
    }

    /** @test */
    public function it_saves_php_translations_of_every_locale()
    {
        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->mockPoeditorDownload('en', [
            'first-en-php-file' => [
                'foo' => 'bar',
            ],
            'second-en-php-file' => [
                'foo_bar' => 'bar foo',
            ],
        ]);

        $this->mockPoeditorDownload('nl', [
            'first-nl-php-file' => [
                'bar_foo' => 'foo bar',
            ],
            'second-nl-php-file' => [
                'bar' => 'foo',
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertPhpTranslationFile('en/first-en-php-file.php', ['foo' => 'bar']);
        $this->assertPhpTranslationFile('en/second-en-php-file.php', ['foo_bar' => 'bar foo']);
        $this->assertPhpTranslationFile('nl/first-nl-php-file.php', ['bar_foo' => 'foo bar']);
        $this->assertPhpTranslationFile('nl/second-nl-php-file.php', ['bar' => 'foo']);
    }

    /** @test */
    public function it_saves_json_translations_of_every_locale()
    {
        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->mockPoeditorDownload('en', [
            'first-en-json-key' => 'bar',
            'second-en-json-key' => 'bar foo',
        ]);

        $this->mockPoeditorDownload('nl', [
            'first-nl-json-key' => 'foo bar',
            'second-nl-json-key' => 'foo',
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertJsonTranslationFile('en.json', [
            'first-en-json-key' => 'bar',
            'second-en-json-key' => 'bar foo',
        ]);
        $this->assertJsonTranslationFile('nl.json', [
            'first-nl-json-key' => 'foo bar',
            'second-nl-json-key' => 'foo',
        ]);
    }

    /** @test */
    public function it_saves_vendor_php_and_json_translations_of_every_locale()
    {
        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->mockPoeditorDownload('en', [
            'vendor' => [
                'first-package' => [
                    'first-package-en-php-file' => [
                        'foo' => 'bar',
                    ],
                    'first-package-first-en-json-key' => 'bar foo',
                    'first-package-second-en-json-key' => 'foo bar',
                ],
                'second-package' => [
                    'second-package-en-php-file' => [
                        'bar' => 'foo',
                    ],
                    'second-package-first-en-json-key' => 'bar foo bar',
                    'second-package-second-en-json-key' => 'foo bar foo',
                ],
            ],
        ]);

        $this->mockPoeditorDownload('nl', [
            'vendor' => [
                'first-package' => [
                    'first-package-nl-php-file' => [
                        'bar' => 'foo',
                    ],
                    'first-package-first-nl-json-key' => 'foo bar',
                    'first-package-second-nl-json-key' => 'bar foo',
                ],
                'second-package' => [
                    'second-package-nl-php-file' => [
                        'foo' => 'bar',
                    ],
                    'second-package-first-nl-json-key' => 'foo bar foo',
                    'second-package-second-nl-json-key' => 'bar foo bar',
                ],
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileDoesntExist('en/vendor.php');
        $this->assertTranslationFileDoesntExist('nl/vendor.php');

        $this->assertPhpTranslationFile('vendor/first-package/en/first-package-en-php-file.php', ['foo' => 'bar']);
        $this->assertPhpTranslationFile('vendor/second-package/en/second-package-en-php-file.php', ['bar' => 'foo']);

        $this->assertJsonTranslationFile('vendor/first-package/en.json', [
            'first-package-first-en-json-key' => 'bar foo',
            'first-package-second-en-json-key' => 'foo bar',
        ]);
        $this->assertJsonTranslationFile('vendor/second-package/en.json', [
            'second-package-first-en-json-key' => 'bar foo bar',
            'second-package-second-en-json-key' => 'foo bar foo',
        ]);

        $this->assertPhpTranslationFile('vendor/first-package/nl/first-package-nl-php-file.php', ['bar' => 'foo']);
        $this->assertPhpTranslationFile('vendor/second-package/nl/second-package-nl-php-file.php', ['foo' => 'bar']);

        $this->assertJsonTranslationFile('vendor/first-package/nl.json', [
            'first-package-first-nl-json-key' => 'foo bar',
            'first-package-second-nl-json-key' => 'bar foo',
        ]);
        $this->assertJsonTranslationFile('vendor/second-package/nl.json', [
            'second-package-first-nl-json-key' => 'foo bar foo',
            'second-package-second-nl-json-key' => 'bar foo bar',
        ]);
    }

    /** @test */
    public function it_saves_php_and_json_and_vendor_translations_of_locale()
    {
        $this->mockPoeditorDownload('en', [
            'php-file' => [
                'foo' => 'bar',
            ],
            'json-key' => 'bar foo',
            'vendor' => [
                'package-name' => [
                    'package-php-file' => [
                        'bar' => 'foo',
                    ],
                    'package-json-key' => 'foo bar foo',
                ],
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileDoesntExist('en/vendor.php');

        $this->assertPhpTranslationFile('en/php-file.php', ['foo' => 'bar']);
        $this->assertJsonTranslationFile('en.json', ['json-key' => 'bar foo']);
        $this->assertPhpTranslationFile('vendor/package-name/en/package-php-file.php', ['bar' => 'foo']);
        $this->assertJsonTranslationFile('vendor/package-name/en.json', ['package-json-key' => 'foo bar foo']);
    }

    /** @test */
    public function it_removes_old_php_translations_of_locale()
    {
        $this->createPhpTranslationFile('en/old-php-file.php', ['foo' => 'bar']);

        $this->mockPoeditorDownload('en', [
            'new-php-file' => [
                'bar' => 'foo',
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileDoesntExist('en/old-php-file.php');
        $this->assertTranslationFileExists('en/new-php-file.php');
    }

    /** @test */
    public function it_does_not_remove_php_translations_of_locale_if_php_translation_is_excluded_in_config()
    {
        $this->createPhpTranslationFile('en/excluded-php-file.php', ['foo' => 'bar']);
        $this->createPhpTranslationFile('en/not-excluded-php-file.php', ['foo' => 'bar']);

        config()->set('poeditor-sync.excluded_files', ['excluded-php-file.php']);

        $this->mockPoeditorDownload('en', [
            'some-php-file' => [
                'foo' => 'bar',
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileExists('en/excluded-php-file.php');
        $this->assertTranslationFileDoesntExist('en/not-excluded-php-file.php');
        $this->assertTranslationFileExists('en/some-php-file.php');
    }

    /** @test */
    public function it_overrides_old_json_translation_of_locale()
    {
        $this->createJsonTranslationFile('en.json', ['foo' => 'bar']);

        $this->mockPoeditorDownload('en', ['bar' => 'foo']);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertJsonTranslationFile('en.json', ['bar' => 'foo']);
    }

    /** @test */
    public function it_removes_old_php_vendor_translations_of_locale()
    {
        $this->createPhpTranslationFile('vendor/package-name/en/old-php-file.php', ['foo' => 'bar']);

        $this->mockPoeditorDownload('en', [
            'vendor' => [
                'package-name' => [
                    'new-php-file' => [
                        'bar' => 'foo',
                    ],
                ],
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileDoesntExist('vendor/package-name/en/old-php-file.php');
        $this->assertTranslationFileExists('vendor/package-name/en/new-php-file.php');
    }

    /** @test */
    public function it_overrides_old_json_vendor_translations_of_locale()
    {
        $this->createJsonTranslationFile('vendor/package-name/en.json', ['foo' => 'bar']);

        $this->mockPoeditorDownload('en', [
            'vendor' => [
                'package-name' => [
                    'bar' => 'foo',
                ],
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertJsonTranslationFile('vendor/package-name/en.json', ['bar' => 'foo']);
    }

    /** @test */
    public function it_does_not_save_vendor_translations_if_disabled_in_config()
    {
        config()->set('poeditor-sync.include_vendor', false);

        $this->mockPoeditorDownload('en', [
            'php-file' => [
                'bar' => 'foo',
            ],
            'json-key' => 'bar foo bar',
            'vendor' => [
                'package-name' => [
                    'php-file' => [
                        'bar' => 'foo',
                    ],
                    'json-key' => 'foo bar foo',
                ],
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileDoesntExist('vendor/package-name/en/php-file.php');
        $this->assertTranslationFileDoesntExist('vendor/package-name/en.json');
        $this->assertTranslationFileDoesntExist('en/vendor.php');

        $this->assertTranslationFileExists('en/php-file.php');
        $this->assertTranslationFileExists('en.json');
    }

    /** @test */
    public function it_does_not_create_json_translation_file_if_no_json_translations_present()
    {
        $this->mockPoeditorDownload('en', [
            'php-file' => [
                'bar' => 'foo',
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertTranslationFileExists('en/php-file.php');
        $this->assertTranslationFileDoesntExist('en.json');
    }

    /** @test */
    public function it_maps_poeditor_locales_on_internal_locales()
    {
        config()->set('poeditor-sync.locales', ['en-gb' => 'en', 'nl-be' => 'nl']);

        $this->mockPoeditorDownload('en-gb', [
            'en-php-file' => [
                'foo' => 'bar',
            ],
            'foo bar' => 'bar foo',
        ]);

        $this->mockPoeditorDownload('nl-be', [
            'nl-php-file' => [
                'bar' => 'foo',
            ],
            'bar foo' => 'foo bar',
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertPhpTranslationFile('en/en-php-file.php', ['foo' => 'bar']);
        $this->assertJsonTranslationFile('en.json', ['foo bar' => 'bar foo']);
        $this->assertPhpTranslationFile('nl/nl-php-file.php', ['bar' => 'foo']);
        $this->assertJsonTranslationFile('nl.json', ['bar foo' => 'foo bar']);
    }

    /** @test */
    public function it_removes_empty_translations()
    {
        config()->set('poeditor-sync.locales', ['en', 'fr']);

        $this->mockPoeditorDownload('en', [
            'php-file' => [
                'foo' => 'bar',
                'bar' => '',
            ],
        ]);

        $this->mockPoeditorDownload('fr', [
            'php-file' => [
                'foo' => '',
                'bar' => 'baz',
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertPhpTranslationFile(lang_path('en/php-file.php'), ['foo' => 'bar']);
        $this->assertPhpTranslationFile(lang_path('fr/php-file.php'), ['bar' => 'baz']);
    }

    /** @test */
    public function it_maps_poeditor_locales_on_multiple_internal_locales()
    {
        config()->set('app.fallback_locale', 'en_GB');
        config()->set('poeditor-sync.locales', ['en' => 'en_GB', 'nl' => ['nl_BE', 'nl_NL']]);

        $this->mockPoeditorDownload('en', [
            'en-php-file' => [
                'foo' => 'bar',
            ],
            'foo bar' => 'bar foo',
        ]);

        $this->mockPoeditorDownload('nl', [
            'nl-php-file' => [
                'bar' => 'foo',
            ],
            'bar foo' => 'foo bar',
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertPhpTranslationFile('en_GB/en-php-file.php', ['foo' => 'bar']);
        $this->assertJsonTranslationFile('en_GB.json', ['foo bar' => 'bar foo']);
        $this->assertPhpTranslationFile('nl_BE/nl-php-file.php', ['bar' => 'foo']);
        $this->assertJsonTranslationFile('nl_BE.json', ['bar foo' => 'foo bar']);
        $this->assertPhpTranslationFile('nl_NL/nl-php-file.php', ['bar' => 'foo']);
        $this->assertJsonTranslationFile('nl_NL.json', ['bar foo' => 'foo bar']);
    }

    /** @test */
    public function it_automatically_runs_validate_command_after_download_if_configured()
    {
        $this->mockPoeditorDownload('en', ['foo' => 'bar']);

        config()->set('poeditor-sync.validate_after_download', false);

        $this->artisan('poeditor:download')
            ->expectsOutput('All translations have been downloaded!')
            ->doesntExpectOutput('All translations are valid!')
            ->assertExitCode(0);

        config()->set('poeditor-sync.validate_after_download', true);

        $this->artisan('poeditor:download')
            ->expectsOutput('All translations have been downloaded!')
            ->expectsOutput('All translations are valid!')
            ->assertExitCode(0);
    }

    public function mockPoeditorDownload(string $language, array $data) : void
    {
        if (get_class(app(Poeditor::class)) === Poeditor::class) {
            $this->mock(Poeditor::class);
        }

        app(Poeditor::class)->shouldReceive('download')->with($language)->andReturn($data);
    }
}
