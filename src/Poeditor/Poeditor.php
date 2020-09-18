<?php

namespace NextApps\PoeditorSync\Poeditor;

use GuzzleHttp\Client;
use InvalidArgumentException;

class Poeditor
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $projectId;

    /**
     * Create a new manager instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param string $apiKey
     * @param string $projectId
     *
     * @return void
     */
    public function __construct(Client $client, $apiKey, $projectId)
    {
        if (! is_string($apiKey) || ! $apiKey) {
            throw new InvalidArgumentException('Invalid API key');
        }

        if (! is_string($projectId) || ! $projectId) {
            throw new InvalidArgumentException('Invalid project id');
        }

        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
    }

    /**
     * Download translations in the language.
     *
     * @param string $language
     *
     * @return array
     */
    public function download(string $language)
    {
        $projectResponse = $this->client
            ->post(
                'https://api.poeditor.com/v2/projects/export',
                [
                    'form_params' => [
                        'api_token' => $this->apiKey,
                        'id' => $this->projectId,
                        'language' => $language,
                        'type' => 'key_value_json',
                    ],
                ]
            )
            ->getBody()
            ->getContents();

        $exportUrl = json_decode($projectResponse, true)['result']['url'];

        $exportResponse = $this->client
            ->get($exportUrl)
            ->getBody()
            ->getContents();

        return json_decode($exportResponse, true);
    }

    /**
     * Upload translations in the language.
     *
     * @param string $language
     * @param array $translations
     * @param bool $overwrite
     *
     * @return \NextApps\PoeditorSync\Poeditor\UploadResponse
     */
    public function upload(string $language, array $translations, bool $overwrite = false)
    {
        $filename = stream_get_meta_data($file = tmpfile())['uri'].'.json';

        file_put_contents($filename, json_encode($translations));

        $response = $this->client
            ->post(
                'https://api.poeditor.com/v2/projects/upload',
                [
                    'multipart' => [
                        [
                            'name' => 'api_token',
                            'contents' => $this->apiKey,
                        ],
                        [
                            'name' => 'id',
                            'contents' => $this->projectId,
                        ],
                        [
                            'name' => 'language',
                            'contents' => $language,
                        ],
                        [
                            'name' => 'updating',
                            'contents' => 'terms_translations',
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($filename, 'r+'),
                            'filename' => 'translations.json',
                        ],
                        [
                            'name' => 'overwrite',
                            'contents' => (int) $overwrite,
                        ],
                        [
                            'name' => 'fuzzy_trigger',
                            'contents' => 1,
                        ],
                    ],
                ]
            )
            ->getBody()
            ->getContents();

        return new UploadResponse(json_decode($response, true));
    }
}
