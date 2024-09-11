<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class YoutubeScrapperService 
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://youtube342.p.rapidapi.com/',
            'headers' => [
                'X-RapidAPI-Host' => 'youtube342.p.rapidapi.com',
                'X-RapidAPI-Key' => config('rapidapi.rapid_api_key')
            ],
        ]);
    }

    public function getData(string $tweetId): ?array 
    {
        $data = $this->getInfoByID($tweetId);
        return $this->prepareData($data);
    }

    protected function prepareData($data): ?array 
    {
        if (empty($data) || empty($data->items[0])) {
            return null;
        }
        $videoData = $data->items[0];
        $statistics = $videoData->statistics;
        $snippet = $videoData->snippet;
        
        return [
            'view' => $statistics->viewCount ?? 0,
            'like' => $statistics->likeCount ?? 0,
            'comment' => $statistics->commentCount ?? 0,
            'upload_date' => $snippet->publishedAt ?? null, 
        ];
    }

    protected function getInfoByID(string $videoId): mixed 
    {
        try {
            $response = $this->client->request('GET', 'videos', [
                'query' => ['part' => 'snippet,statistics', 'id' => $videoId],
            ]);
            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            Log::error('Error fetching tweet by ID: '.$e);
            return null;
        }
    }
}