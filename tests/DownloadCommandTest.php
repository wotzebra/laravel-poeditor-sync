<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use Symfony\Component\VarExporter\VarExporter;

class DownloadCommandTest extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
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

        $this->assertPhpTranslationFile(lang_path('en/first-en-php-file.php'), ['foo' => 'bar']);
        $this->assertPhpTranslationFile(lang_path('en/second-en-php-file.php'), ['foo_bar' => 'bar foo']);
        $this->assertPhpTranslationFile(lang_path('nl/first-nl-php-file.php'), ['bar_foo' => 'foo bar']);
        $this->assertPhpTranslationFile(lang_path('nl/second-nl-php-file.php'), ['bar' => 'foo']);
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

        $this->assertJsonTranslationFile(lang_path('en.json'), [
            'first-en-json-key' => 'bar',
            'second-en-json-key' => 'bar foo',
        ]);
        $this->assertJsonTranslationFile(lang_path('nl.json'), [
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

        $this->assertFalse(file_exists(lang_path('en/vendor.php')));
        $this->assertFalse(file_exists(lang_path('nl/vendor.php')));

        $this->assertPhpTranslationFile(
            lang_path('vendor/first-package/en/first-package-en-php-file.php'),
            ['foo' => 'bar']
        );
        $this->assertJsonTranslationFile(lang_path('vendor/first-package/en.json'), [
            'first-package-first-en-json-key' => 'bar foo',
            'first-package-second-en-json-key' => 'foo bar',
        ]);
        $this->assertPhpTranslationFile(
            lang_path('vendor/second-package/en/second-package-en-php-file.php'),
            ['bar' => 'foo']
        );
        $this->assertJsonTranslationFile(lang_path('vendor/second-package/en.json'), [
            'second-package-first-en-json-key' => 'bar foo bar',
            'second-package-second-en-json-key' => 'foo bar foo',
        ]);

        $this->assertPhpTranslationFile(
            lang_path('vendor/first-package/nl/first-package-nl-php-file.php'),
            ['bar' => 'foo']
        );
        $this->assertJsonTranslationFile(lang_path('vendor/first-package/nl.json'), [
            'first-package-first-nl-json-key' => 'foo bar',
            'first-package-second-nl-json-key' => 'bar foo',
        ]);
        $this->assertPhpTranslationFile(
            lang_path('vendor/second-package/nl/second-package-nl-php-file.php'),
            ['foo' => 'bar']
        );
        $this->assertJsonTranslationFile(lang_path('vendor/second-package/nl.json'), [
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

        $this->assertFalse(file_exists(lang_path('en/vendor.php')));

        $this->assertPhpTranslationFile(lang_path('en/php-file.php'), ['foo' => 'bar']);
        $this->assertJsonTranslationFile(lang_path('en.json'), ['json-key' => 'bar foo']);
        $this->assertPhpTranslationFile(lang_path('vendor/package-name/en/package-php-file.php'), ['bar' => 'foo']);
        $this->assertJsonTranslationFile(lang_path('vendor/package-name/en.json'), ['package-json-key' => 'foo bar foo']);
    }

    /** @test */
    public function it_removes_old_php_translations_of_locale()
    {
        file_put_contents(lang_path('en/old-php-file.php'), ['foo' => 'bar']);

        $this->mockPoeditorDownload('en', [
            'new-php-file' => [
                'bar' => 'foo',
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertFalse(file_exists(lang_path('en/old-php-file.php')));
        $this->assertTrue(file_exists(lang_path('en/new-php-file.php')));
    }

    /** @test */
    public function it_overrides_old_json_translation_of_locale()
    {
        file_put_contents(lang_path('en.json'), json_encode(['foo' => 'bar'], JSON_PRETTY_PRINT));

        $this->mockPoeditorDownload('en', ['bar' => 'foo']);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertJsonTranslationFile(lang_path('en.json'), ['bar' => 'foo']);
    }

    /** @test */
    public function it_removes_old_php_vendor_translations_of_locale()
    {
        app(Filesystem::class)->makeDirectory(lang_path('vendor/package-name/en/'), 0755, true);

        file_put_contents(lang_path('vendor/package-name/en/old-php-file.php'), ['foo' => 'bar']);

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

        $this->assertFalse(file_exists(lang_path('vendor/package-name/en/old-php-file.php')));
        $this->assertTrue(file_exists(lang_path('vendor/package-name/en/new-php-file.php')));
    }

    /** @test */
    public function it_overrides_old_json_vendor_translations_of_locale()
    {
        app(Filesystem::class)->makeDirectory(lang_path('vendor/package-name'), 0755, true);

        file_put_contents(lang_path('vendor/package-name/en.json'), json_encode(['foo' => 'bar'], JSON_PRETTY_PRINT));

        $this->mockPoeditorDownload('en', [
            'vendor' => [
                'package-name' => [
                    'bar' => 'foo',
                ],
            ],
        ]);

        $this->artisan('poeditor:download')->assertExitCode(0);

        $this->assertJsonTranslationFile(lang_path('vendor/package-name/en.json'), ['bar' => 'foo']);
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

        $this->assertFalse(file_exists(lang_path('vendor/package-name/en/php-file.php')));
        $this->assertFalse(file_exists(lang_path('vendor/package-name/en.json')));
        $this->assertFalse(file_exists(lang_path('en/vendor.php')));

        $this->assertTrue(file_exists(lang_path('en/php-file.php')));
        $this->assertTrue(file_exists(lang_path('en.json')));
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

        $this->assertTrue(file_exists(lang_path('en/php-file.php')));
        $this->assertFalse(file_exists(lang_path('en.json')));
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

        $this->assertPhpTranslationFile(lang_path('en/en-php-file.php'), ['foo' => 'bar']);
        $this->assertJsonTranslationFile(lang_path('en.json'), ['foo bar' => 'bar foo']);
        $this->assertPhpTranslationFile(lang_path('nl/nl-php-file.php'), ['bar' => 'foo']);
        $this->assertJsonTranslationFile(lang_path('nl.json'), ['bar foo' => 'foo bar']);
    }

    /**
     * Mock the POEditor "download" method.
     *
     * @param string $language
     * @param array $data
     *
     * @return void
     */
    public function mockPoeditorDownload(string $language, array $data)
    {
        if (get_class(app(Poeditor::class)) !== Poeditor::class) {
            app(Poeditor::class)->shouldReceive('download')
                ->with($language)
                ->andReturn($data);

            return;
        }

        $this->mock(Poeditor::class, function ($mock) use ($language, $data) {
            $mock->shouldReceive('download')
                ->with($language)
                ->andReturn($data);
        });
    }

    /**
     * Assert that PHP translation file exists and contains data.
     *
     * @param string $filename
     * @param array $data
     *
     * @return void
     */
    public function assertPhpTranslationFile(string $filename, array $data)
    {
        $this->assertTrue(file_exists($filename));
        $this->assertEquals(
            '<?php'.PHP_EOL.PHP_EOL.'return '.VarExporter::export($data).';'.PHP_EOL,
            file_get_contents($filename)
        );
    }

    /**
     * Assert that JSON translation file exists and contains data.
     *
     * @param string $filename
     * @param array $data
     *
     * @return void
     */
    public function assertJsonTranslationFile(string $filename, array $data)
    {
        $this->assertTrue(file_exists($filename));
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), file_get_contents($filename));
    }
}
