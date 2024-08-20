<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TwitterScrapperService 
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://twitter-api45.p.rapidapi.com/',
            'headers' => [
                'X-RapidAPI-Host' => 'twitter-api45.p.rapidapi.com',
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
        if (empty($data)) {
            return null;
        }
        return [
            'like' => $data->likes ?? 0,
            'retweets' => $data->retweets ?? 0,
            'comment' => $data->replies ?? 0,
            'bookmarks' => $data->bookmarks ?? 0,
            'quotes' => $data->quotes ?? 0,
            'view' => $data->views ?? 0,
            'author' => $data->author->screen_name ?? 'Unknown',
            'text' => $data->text ?? '',
            'upload_date' => $data->created_at ?? null,
        ];
    }

    protected function getInfoByID(string $tweetId): mixed 
    {
        try {
            $response = $this->client->request('GET', 'tweet.php', [
                'query' => ['id' => $tweetId],
            ]);
            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            Log::error('Error fetching tweet by ID: '.$e);
            return null;
        }
    }
}