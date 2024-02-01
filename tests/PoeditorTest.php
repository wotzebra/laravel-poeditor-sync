<?php

namespace NextApps\PoeditorSync\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use InvalidArgumentException;
use NextApps\PoeditorSync\Poeditor\Poeditor;
use NextApps\PoeditorSync\Poeditor\UploadResponse;

class PoeditorTest extends TestCase
{
    /** @test */
    public function it_requests_export_url_and_downloads_its_content()
    {
        Http::fake([
            'https://api.poeditor.com/v2/projects/export' => Http::response([
                'response' => [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                ],
                'result' => [
                    'url' => $exportUrl = $this->faker->url(),
                ],
            ]),
            $exportUrl => Http::response([
                'key' => 'value',
            ]),
        ]);

        $translations = app(Poeditor::class)->download($locale = $this->faker->locale());

        $this->assertEquals(['key' => 'value'], $translations);

        Http::assertSent(function (Request $request) use ($locale) {
            return $request->url() === 'https://api.poeditor.com/v2/projects/export'
                && $request->method() === 'POST'
                && $request->isForm()
                && $request->data() === [
                    'api_token' => config('poeditor-sync.api_key'),
                    'id' => config('poeditor-sync.project_id'),
                    'language' => $locale,
                    'type' => 'key_value_json',
                ];
        });
    }

    /** @test */
    public function it_downloads_its_content_and_filters_empty_strings_out()
    {
        Http::fake([
            'https://api.poeditor.com/v2/projects/export' => Http::response([
                'response' => [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                ],
                'result' => [
                    'url' => $exportUrl = $this->faker->url(),
                ],
            ]),
            $exportUrl => Http::response([
                'key' => 'value',
                'empty' => '',
                'nested' => [
                    'empty' => [
                        'value' => ''
                    ]
                ]
            ]),
        ]);

        $translations = app(Poeditor::class)->download($locale = $this->faker->locale());

        $this->assertEquals(['key' => 'value'], $translations);

        Http::assertSent(function (Request $request) use ($locale) {
            return $request->url() === 'https://api.poeditor.com/v2/projects/export'
                && $request->method() === 'POST'
                && $request->isForm()
                && $request->data() === [
                    'api_token' => config('poeditor-sync.api_key'),
                    'id' => config('poeditor-sync.project_id'),
                    'language' => $locale,
                    'type' => 'key_value_json',
                ];
        });
    }



    /** @test */
    public function it_throws_an_error_if_api_key_is_empty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid API key');
        config()->set('poeditor-sync.api_key', '');

        app(Poeditor::class)->download($this->faker->locale());
    }

    /** @test */
    public function it_throws_an_error_if_project_id_is_empty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid project id');
        config()->set('poeditor-sync.project_id', '');

        app(Poeditor::class)->download($this->faker->locale());
    }

    /** @test */
    public function it_uploads_translations()
    {
        Http::fake([
            'https://api.poeditor.com/v2/projects/upload' => Http::response([
                'response' => [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                ],
                'result' => [
                    'terms' => [
                        'parsed' => $this->faker->randomNumber(),
                        'added' => $this->faker->randomNumber(),
                        'deleted' => $this->faker->randomNumber(),
                    ],
                    'translations' => [
                        'parsed' => $this->faker->randomNumber(),
                        'added' => $this->faker->randomNumber(),
                        'updated' => $this->faker->randomNumber(),
                    ],
                ],
            ]),
        ]);

        $response = app(Poeditor::class)->upload($locale = $this->faker->locale(), $translations = ['key' => 'value']);

        $this->assertInstanceOf(UploadResponse::class, $response);

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) use ($locale, $translations) {
            return $request->url() === 'https://api.poeditor.com/v2/projects/upload'
                && $request->method() === 'POST'
                && $request->isMultipart()
                && $request->data() === [
                    [
                        'name' => 'api_token',
                        'contents' => config('poeditor-sync.api_key'),
                    ],
                    [
                        'name' => 'id',
                        'contents' => config('poeditor-sync.project_id'),
                    ],
                    [
                        'name' => 'language',
                        'contents' => $locale,
                    ],
                    [
                        'name' => 'updating',
                        'contents' => 'terms_translations',
                    ],
                    [
                        'name' => 'overwrite',
                        'contents' => 0,
                    ],
                    [
                        'name' => 'fuzzy_trigger',
                        'contents' => 1,
                    ],
                    [
                        'name' => 'file',
                        'contents' => json_encode($translations),
                        'filename' => 'translations.json',
                    ],
                ];
        });
    }

    /** @test */
    public function it_retries_uploads_translations_if_poeditor_upload_rate_limit_was_hit()
    {
        Http::fake([
            'https://api.poeditor.com/v2/projects/upload' => Http::sequence()
                ->push([
                    'response' => [
                        'status' => 'fail',
                        'code' => '4048',
                        'message' => 'Too many upload requests in a short period of time',
                    ],
                ])
                ->push([
                    'response' => [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'OK',
                    ],
                    'result' => [
                        'terms' => [
                            'parsed' => $this->faker->randomNumber(),
                            'added' => $this->faker->randomNumber(),
                            'deleted' => $this->faker->randomNumber(),
                        ],
                        'translations' => [
                            'parsed' => $this->faker->randomNumber(),
                            'added' => $this->faker->randomNumber(),
                            'updated' => $this->faker->randomNumber(),
                        ],
                    ],
                ]),
        ]);

        if (class_exists(Sleep::class)) {
            Sleep::fake();
        } else {
            $startTime = time();
        }

        $response = app(Poeditor::class)->upload($this->faker->locale(), ['key' => 'value']);

        $this->assertInstanceOf(UploadResponse::class, $response);

        Http::assertSentCount(2);

        if (class_exists(Sleep::class)) {
            Sleep::assertSleptTimes(1);
            Sleep::assertSequence([Sleep::for(10)->seconds()]);
        } else {
            $this->assertTrue(time() - $startTime > 9);
        }
    }
}
