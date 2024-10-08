<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InstagramScrapperService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://instagram-scraper-2022.p.rapidapi.com/ig/',
            'headers' => [
                'X-RapidAPI-Host' => 'instagram-scraper-2022.p.rapidapi.com',
                'X-RapidAPI-Key' => config('rapidapi.rapid_api_key')
            ],
        ]);
    }

    public function getPostInfo($link): ?array
    {
        try {
            $shortCode = $this->extractShortCode($link);

            $response = $this->client->request('GET', 'post_info/', [
                'query' => ['shortcode' => $shortCode],
            ]);

            $data = json_decode($response->getBody()->getContents());

            return [
                'comment' => $data->data->xdt_api__v1__media__shortcode__web_info->items[0]->comment_count ?? 0,
                'view' => $data->data->xdt_api__v1__media__shortcode__web_info->items[0]->view_count ?? 0,
                'like' => $data->data->xdt_api__v1__media__shortcode__web_info->items[0]->like_count ?? 0,
                'upload_date' => $data->data->xdt_api__v1__media__shortcode__web_info->items[0]->taken_at ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching IG info: ' . $e);
            return null;
        }
    }

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
