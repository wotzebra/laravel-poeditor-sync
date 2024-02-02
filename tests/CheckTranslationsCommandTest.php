<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use NextApps\PoeditorSync\Poeditor\Poeditor;

class CheckTranslationsCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app(Filesystem::class)->cleanDirectory(lang_path());
        app(Filesystem::class)->makeDirectory(lang_path('en'));
    }

    /** @test */
    public function it_checks_if_local_translations_match_downloaded_translations()
    {
        config()->set('poeditor-sync.locales', ['en', 'nl']);

        $this->createPhpTranslationFile('en/php-file.php', ['foo' => 'bar', 'nested' => ['value' => 'nested value'], 'baz' => 'bar']);

        $this->mockPoeditorDownload('en', [
            'php-file' => [
                'foo' => 'bar',
                'nested' => [
                    'value' => 'nested value',
                ],
            ],
        ]);

        $this->mockPoeditorDownload('nl', [
            'php-file' => [
                'bar_foo' => 'foo bar',
            ],
        ]);

        $this->artisan('poeditor:check')
            ->expectsOutput('Checking translations for en')
            ->expectsOutput('The translations for en do not match the ones on POEditor')
            ->expectsOutput('Checking translations for nl')
            ->expectsOutput('The translations for nl do not match the ones on POEditor')
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
