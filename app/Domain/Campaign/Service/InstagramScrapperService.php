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
                'X-RapidAPI-Key' => config('rapidapi.rapid_api_key', '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986')
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
                Log::error('InstagramScrapperService: Failed to extract shortcode', ['link' => $link]);
                return null;
            }
            
            $response = $this->client->request('GET', 'ig/post_info_v2/', [
                'query' => [
                    'nocors' => 'false',
                    'shortcode' => $shortCode
                ],
            ]);
            
            $content = $response->getBody()->getContents();
            $data = json_decode($content);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('InstagramScrapperService: JSON decode error', ['error' => json_last_error_msg()]);
                return null;
            }
            
            if (!isset($data->items) || empty($data->items)) {
                Log::error('InstagramScrapperService: No items in response', ['shortCode' => $shortCode]);
                return null;
            }
            
            $item = $data->items[0];
            
            // Extract the date from "taken_at" timestamp
            $uploadDate = null;
            if (isset($item->taken_at)) {
                // Convert Unix timestamp to Carbon date
                $uploadDate = Carbon::createFromTimestamp($item->taken_at)->toDateTimeString();
            }
            
            // Get view count from appropriate field
            $viewCount = 0;
            if (isset($item->play_count)) {
                $viewCount = $item->play_count;
            } elseif (isset($item->ig_play_count)) {
                $viewCount = $item->ig_play_count;
            } elseif (isset($item->view_count)) {
                $viewCount = $item->view_count;
            }
            
            return [
                'comment' => $item->comment_count ?? 0,
                'view' => $viewCount,
                'like' => $item->like_count ?? 0,
                'upload_date' => $uploadDate
            ];
            
        } catch (\Exception $e) {
            // Log the specific error details with enough context to debug
            Log::error('InstagramScrapperService error: ' . $e->getMessage(), [
                'link' => $link,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function getFinalUrl($url): string
    {
        try {
            // Use HTTP client with timeout to avoid hanging
            $response = Http::timeout(10)->get($url);
            return $response->effectiveUri();
        } catch (\Exception $e) {
            Log::error('Error resolving Instagram URL: ' . $e->getMessage());
            return $url; // Return original URL as fallback
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