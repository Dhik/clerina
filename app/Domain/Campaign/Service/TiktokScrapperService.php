<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TiktokScrapperService
{
    protected Client $client;

    protected const BY_URL = 'byURL';
    protected const BY_ID = 'byId';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://tiktok-video-feature-summary.p.rapidapi.com/',
            'headers' => [
                'X-RapidAPI-Host' => 'tiktok-video-feature-summary.p.rapidapi.com',
                'X-RapidAPI-Key' => config('rapidapi.rapid_api_key')
            ],
        ]);
    }

    public function getData(string $url): ?array
    {
        try {
            $response = $this->client->request('GET', '', [
                'query' => [
                    'url' => $url,
                    'hd' => 1
                ]
            ]);

            $data = json_decode($response->getBody()->getContents());

            return $this->prepareData($data);
        } catch (\Exception $e) {
            Log::error('Error fetching tiktok video: ' . $e->getMessage());
            return null;
        }
    }

    protected function prepareData($apiResponse): ?array
    {
        if (empty($apiResponse) || $apiResponse->code !== 0) {
            return null;
        }

        $data = $apiResponse->data;

        return [
            'comment' => $data->comment_count,
            'view' => $data->play_count,
            'like' => $data->digg_count,
            'share' => $data->share_count,
            'username' => $data->author->unique_id,
            'duration' => $data->duration,
            'upload_date' => $data->create_time,
        ];
    }
}