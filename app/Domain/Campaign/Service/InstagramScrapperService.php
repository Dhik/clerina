<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramScrapperService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://instagram-scraper-api2.p.rapidapi.com/v1/',
            'headers' => [
                'X-RapidAPI-Host' => 'instagram-scraper-api2.p.rapidapi.com',
                'X-RapidAPI-Key' => config('rapidapi.rapid_api_key')
            ],
            'allow_redirects' => true,
        ]);
    }

    public function getPostInfo($link): ?array
    {
        try {
            $finalUrl = $this->getFinalUrl($link);
            $shortCode = $this->extractShortCode($finalUrl);
            $response = $this->client->request('GET', 'post_info', [
                'query' => ['code_or_id_or_url' => $shortCode],
            ]);

            $data = json_decode($response->getBody()->getContents());

            return [
                'comment' => $data->data->metrics->comment_count ?? 0,
                'view' => $data->data->metrics->play_count ?? 0,
                'like' => $data->data->metrics->like_count ?? 0,
                'upload_date' => $data->data->taken_at ?? null
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching IG info: ' . $e);
            return null;
        }
    }

    protected function getFinalUrl($url): string
    {
        try {
            // Perform the HTTP request and follow redirects automatically
            $response = Http::get($url);

            // Get the final redirected URL
            $finalUrl = $response->effectiveUri();

            return $finalUrl;
        } catch (\Exception $e) {
            Log::error('Error following URL redirect: ' . $e);
            return '';
        }
    }

    // Extract the shortcode (post ID) from the URL
    protected function extractShortCode(string $link): string
    {
        // Define the patterns to match the reel ID or post ID
        $reelPattern = '/\/reel\/([^\/?]+)/';
        $postPattern = '/\/p\/([^\/?]+)/';

        // Perform the regular expression match
        if (preg_match($reelPattern, $link, $matches)) {
            return $matches[1];
        } elseif (preg_match($postPattern, $link, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
