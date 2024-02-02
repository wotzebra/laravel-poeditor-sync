<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;

class StatusCommandTest extends TestCase
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
        $this->createPhpTranslationFile('nl/php-file.php', ['bar_foo' => 'foo bar']);

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

        $this->artisan('poeditor:status')
            ->expectsOutput('The translations for \'en\' do not match the ones on POEditor.')
            ->doesntExpectOutput('The translations for \'nl\' do not match the ones on POEditor.')
            ->assertExitCode(1);

        $this->createPhpTranslationFile('en/php-file.php', ['foo' => 'bar', 'nested' => ['value' => 'nested value']]);

        $this->artisan('poeditor:status')
            ->expectsOutput('All translations match the ones on POEditor!')
            ->doesntExpectOutput('The translations for \'en\' do not match the ones on POEditor.')
            ->doesntExpectOutput('The translations for \'nl\' do not match the ones on POEditor.')
            ->assertExitCode(0);
    }
}
