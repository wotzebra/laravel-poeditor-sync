<?php

namespace NextApps\PoeditorSync\Poeditor;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
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

        return Http::get($exportUrl)->json();
    }

    public function upload(string $language, array $translations, bool $overwrite = false) : UploadResponse
    {
        $response = Http::asMultipart()
            ->attach('file', json_encode($translations), 'translations.json')
            ->post('https://api.poeditor.com/v2/projects/upload', [
                'api_token' => $this->apiKey,
                'id' => $this->projectId,
                'language' => $language,
                'updating' => 'terms_translations',
                'overwrite' => (int) $overwrite,
                'fuzzy_trigger' => 1,
            ]);

        if ($response->json('response.status') === 'fail') {
            if (class_exists(Sleep::class)) {
                Sleep::for(10)->seconds();
            } else {
                sleep(10);
            }

            return $this->upload($language, $translations, $overwrite);
        }

        return new UploadResponse($response->json());
    }
}
