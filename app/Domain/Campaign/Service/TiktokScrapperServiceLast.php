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
            'base_uri' => 'https://tokapi-mobile-version.p.rapidapi.com/v1/',
            'headers' => [
                'X-RapidAPI-Host' => 'tokapi-mobile-version.p.rapidapi.com',
                'X-RapidAPI-Key' => config('rapidapi.rapid_api_key')
            ],
        ]);
    }

    public function getData(string $url): ?array
    {
        $urlCheck = $this->urlCheck($url);

        $data = [];

        if ($urlCheck === self::BY_ID) {
            $postId = $this->extractPostId($url);
            $data = $this->getInfoByID($postId);

        } else if ($urlCheck === self::BY_URL) {
            $data = $this->getInfoByURL($url);
        }

        return $this->prepareData($data);
    }

    protected function prepareData($data): ?array
    {
        if (empty($data)) {
            return null;
        }

        return [
            'comment' => $data->aweme_detail->statistics->comment_count,
            'view' => $data->aweme_detail->statistics->play_count,
            'like' => $data->aweme_detail->statistics->digg_count,
            'share' => $data->aweme_detail->statistics->share_count,
            'username' => $data->aweme_detail->author->unique_id,
            'duration' => $data->aweme_detail->video->duration ?? 0,
            'upload_date' => $data->aweme_detail->create_time ?? null
        ];
    }

    protected function getInfoByID(string $postId): mixed
    {
        try {
            $response = $this->client->request('GET', 'post/'.$postId);

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            Log::error('Error fetching tiktok by ID: ' . $e);
            return null;
        }
    }

    protected function getInfoByURL(string $link): mixed
    {
        try {
            $response = $this->client->request('GET', 'post', [
                'query' => ['video_url' => $link],
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            Log::error('Error fetching tiktok by URL: ' . $e);
            return null;
        }
    }

    protected function urlCheck(string $url): ?string
    {
        // Check if the URL starts with tiktok.com or vt.tiktok.com
        if (str_starts_with($url, 'https://www.tiktok.com/t/')) {
            return self::BY_URL; // Shortened tiktok.com URL
        } elseif (str_starts_with($url, 'https://www.tiktok.com/')) {
            return self::BY_ID;
        } elseif (str_starts_with($url, 'https://vt.tiktok.com/')) {
            return self::BY_URL;
        }

        return null;
    }

    protected function extractPostId(string $url): ?string
    {
        // Regular expression to extract the video ID
        $pattern = '/\/video\/(\d+)(\?|$)/';
        preg_match($pattern, $url, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return null;
    }
}
