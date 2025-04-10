<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InstagramScrapperService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://flashapi1.p.rapidapi.com/',
            'headers' => [
                'X-RapidAPI-Host' => 'flashapi1.p.rapidapi.com',
                'X-RapidAPI-Key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986'
            ],
            'allow_redirects' => true,
        ]);
    }

    public function getPostInfo($link): ?array
    {
        try {
            $finalUrl = $this->getFinalUrl($link);
            $shortCode = $this->extractShortCode($finalUrl);
            
            if (empty($shortCode)) {
                Log::error('Failed to extract shortcode from URL: ' . $link);
                return null;
            }
            
            $response = $this->client->request('GET', 'ig/post_info_v2/', [
                'query' => [
                    'nocors' => 'false',
                    'shortcode' => $shortCode
                ],
            ]);

            $data = json_decode($response->getBody()->getContents());
            
            // Check if we have valid items in the response
            if (!isset($data->items) || empty($data->items)) {
                Log::error('No items in response for IG post: ' . $shortCode);
                return null;
            }
            
            $item = $data->items[0];
            
            // Extract the date from "taken_at" timestamp
            $uploadDate = null;
            if (isset($item->taken_at)) {
                // Convert Unix timestamp to Carbon date
                $uploadDate = Carbon::createFromTimestamp($item->taken_at)->toDateTimeString();
            }
            
            return [
                'comment' => $item->comment_count ?? 0,
                'view' => $item->ig_play_count ?? 0,
                'like' => $item->like_count ?? 0,
                'upload_date' => $uploadDate
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