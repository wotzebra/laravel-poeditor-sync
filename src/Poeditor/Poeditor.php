<?php

namespace Wotz\PoeditorSync\Poeditor;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Poeditor
{
    public function __construct(
        public string $apiKey,
        public string $projectId
    ) {
        throw_if(empty($this->apiKey), new InvalidArgumentException('Invalid API key'));
        throw_if(empty($this->projectId), new InvalidArgumentException('Invalid project id'));
    }

    public function download(string $language) : array
    {
        $exportUrl = Http::asForm()->post('https://api.poeditor.com/v2/projects/export', [
            'api_token' => $this->apiKey,
            'id' => $this->projectId,
            'language' => $language,
            'type' => 'key_value_json',
        ])->json('result.url');

        return collect(Http::get($exportUrl)->json())
            ->mapWithKeys(function ($value, $key) {
                return [(string) Str::of($key)->replace('.', '__ESCAPED_DOT__') => $value];
            })
            ->dot()
            ->filter()
            ->undot()
            ->mapWithKeys(function ($value, $key) {
                return [(string) Str::of($key)->replace('__ESCAPED_DOT__', '.') => $value];
            })
            ->toArray();
    }

    public function upload(
        string $language,
        array $translations,
        bool $overwrite = false,
        bool $sync = false
    ) : UploadResponse {
        $response = Http::asMultipart()
            ->attach('file', json_encode($translations), 'translations.json')
            ->post('https://api.poeditor.com/v2/projects/upload', [
                'api_token' => $this->apiKey,
                'id' => $this->projectId,
                'language' => $language,
                'updating' => 'terms_translations',
                'overwrite' => (int) $overwrite,
                'sync_terms' => (int) $sync,
                'fuzzy_trigger' => 1,
            ]);

        if ($response->json('response.status') === 'fail') {
            Sleep::for(10)->seconds();

            return $this->upload($language, $translations, $overwrite);
        }

        return new UploadResponse($response->json());
    }
}
