<?php

namespace NextApps\PoeditorSync\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use GuzzleHttp\Handler\MockHandler;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Poeditor\UploadResponse;

class PoeditorTest extends TestCase
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    public $requestMockHandler;

    /**
     * @var array
     */
    public $requestHistoryContainer = [];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $stack = HandlerStack::create($this->requestMockHandler = new MockHandler());
        $stack->push(Middleware::history($this->requestHistoryContainer));

        app()->instance(Client::class, new Client(['handler' => $stack]));
    }

    /** @test */
    public function it_requests_export_url_and_downloads_it_content()
    {
        $this->requestMockHandler->append(
            new Response(200, [], json_encode([
                'response' => [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                ],
                'result' => [
                    'url' => $url = $this->faker->url,
                ],
            ])),
            new Response(200, [], json_encode([
                'key' => 'value',
            ])),
        );

        $translations = app(Poeditor::class)->download($locale = $this->faker->locale);

        $this->assertEquals(['key' => 'value'], $translations);

        $getExportUrlRequest = $this->requestHistoryContainer[0]['request'];

        $this->assertEquals('POST', $getExportUrlRequest->getMethod());
        $this->assertEquals('api.poeditor.com', $getExportUrlRequest->getUri()->getHost());
        $this->assertEquals('/v2/projects/export', $getExportUrlRequest->getUri()->getPath());

        $getExportUrlRequestBody = [];
        parse_str($getExportUrlRequest->getBody()->getContents(), $getExportUrlRequestBody);

        $this->assertEquals(config('poeditor-sync.api_key'), $getExportUrlRequestBody['api_token']);
        $this->assertEquals(config('poeditor-sync.project_id'), $getExportUrlRequestBody['id']);
        $this->assertEquals($locale, $getExportUrlRequestBody['language']);
        $this->assertEquals('key_value_json', $getExportUrlRequestBody['type']);

        $downloadExportRequest = $this->requestHistoryContainer[1]['request'];

        $this->assertEquals('GET', $downloadExportRequest->getMethod());
        $this->assertEquals(parse_url($url, PHP_URL_HOST), $downloadExportRequest->getUri()->getHost());
        $this->assertEquals(parse_url($url, PHP_URL_PATH), $downloadExportRequest->getUri()->getPath());
    }

    /** @test */
    public function it_throws_an_error_if_config_values_are_empty()
    {
        $this->expectException(InvalidArgumentException::class);
        config()->set('poeditor-sync.api_key', '');
        config()->set('poeditor-sync.project_id', '');

        app(Poeditor::class)->download($this->faker->locale);
    }


    /** @test */
    public function it_uploads_translations()
    {
        $this->requestMockHandler->append(
            new Response(200, [], json_encode([
                'response' => [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                ],
                'result' => [
                    'terms' => [
                        'parsed' => $this->faker->randomNumber,
                        'added' => $this->faker->randomNumber,
                        'deleted' => $this->faker->randomNumber,
                    ],
                    'translations' => [
                        'parsed' => $this->faker->randomNumber,
                        'added' => $this->faker->randomNumber,
                        'updated' => $this->faker->randomNumber,
                    ],
                ],
            ])),
        );

        $response = app(Poeditor::class)->upload($this->faker->locale, ['key' => 'value']);

        $this->assertInstanceOf(UploadResponse::class, $response);

        $request = $this->requestHistoryContainer[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('api.poeditor.com', $request->getUri()->getHost());
        $this->assertEquals('/v2/projects/upload', $request->getUri()->getPath());
    }
}
