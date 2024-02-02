<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\PoeditorSyncServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Symfony\Component\VarExporter\VarExporter;

class TestCase extends BaseTestCase
{
    use WithFaker;

    protected function setUp() : void
    {
        parent::setUp();

        config()->set('poeditor-sync.api_key', Str::random());
        config()->set('poeditor-sync.project_id', Str::random());
        config()->set('poeditor-sync.locales', ['en']);
        config()->set('poeditor-sync.include_vendor', true);
    }

    protected function getPackageProviders($app) : array
    {
        return [
            PoeditorSyncServiceProvider::class,
        ];
    }

    public function createPhpTranslationFile(string $filename, array $data) : void
    {
        $filename = lang_path($filename);

        app(Filesystem::class)->ensureDirectoryExists(dirname($filename));
        app(Filesystem::class)->put($filename, '<?php' . PHP_EOL . PHP_EOL . 'return ' . VarExporter::export($data) . ';' . PHP_EOL);
    }

    public function createJsonTranslationFile(string $filename, array $data) : void
    {
        $filename = lang_path($filename);

        app(Filesystem::class)->ensureDirectoryExists(dirname($filename));
        app(Filesystem::class)->put($filename, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function assertTranslationFileExists(string $filename) : void
    {
        $this->assertTrue(file_exists(lang_path($filename)));
    }

    public function assertTranslationFileDoesntExist(string $filename) : void
    {
        $this->assertFalse(file_exists(lang_path($filename)));
    }

    public function assertPhpTranslationFile(string $filename, array $data) : void
    {
        $filename = lang_path($filename);

        $this->assertTrue(file_exists($filename));
        $this->assertEquals(
            '<?php' . PHP_EOL . PHP_EOL . 'return ' . VarExporter::export($data) . ';' . PHP_EOL,
            file_get_contents($filename)
        );
    }

    public function assertJsonTranslationFile(string $filename, array $data) : void
    {
        $filename = lang_path($filename);

        $this->assertTrue(file_exists($filename));
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), file_get_contents($filename));
    }

    public function mockPoeditorDownload(string $language, array $data) : void
    {
        if (get_class(app(Poeditor::class)) === Poeditor::class) {
            $this->mock(Poeditor::class);
        }

        app(Poeditor::class)->shouldReceive('download')->with($language)->andReturn($data);
    }
}
