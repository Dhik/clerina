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
        Log::error('INSTAGRAM_TEST_MARKER: Starting to process ' . $link);
        
        $finalUrl = $this->getFinalUrl($link);
        $shortCode = $this->extractShortCode($finalUrl);
        
        if (empty($shortCode)) {
            Log::error('InstagramScrapperService: Failed to extract shortcode from URL', ['link' => $link]);
            return null;
        }
        
        try {
            $response = $this->client->request('GET', 'ig/post_info_v2/', [
                'query' => [
                    'nocors' => 'false',
                    'shortcode' => $shortCode
                ],
            ]);
            
            $responseContent = $response->getBody()->getContents();
            $data = json_decode($responseContent);
            
            if (!$data || !isset($data->items) || empty($data->items)) {
                Log::error('InstagramScrapperService: Invalid response or no items', [
                    'shortCode' => $shortCode,
                    'response' => substr($responseContent, 0, 500) . '...' // Log just a portion to avoid huge logs
                ]);
                return null;
            }
            
            return $this->prepareData($data);
            
        } catch (\Exception $e) {
            Log::error('InstagramScrapperService: API request error', [
                'error' => $e->getMessage(),
                'shortCode' => $shortCode
            ]);
            return null;
        }
    }

    protected function prepareData($apiResponse): ?array
    {
        if (empty($apiResponse) || empty($apiResponse->items[0])) {
            return null;
        }
        
        $item = $apiResponse->items[0];
        
        // Extract the date from "taken_at" timestamp
        $uploadDate = null;
        if (isset($item->taken_at)) {
            $uploadDate = Carbon::createFromTimestamp($item->taken_at)->toDateTimeString();
        }
        
        // Get view count - try different possible fields
        $viewCount = 0;
        if (isset($item->play_count)) {
            $viewCount = $item->play_count;
        } elseif (isset($item->ig_play_count)) {
            $viewCount = $item->ig_play_count;
        } elseif (isset($item->view_count)) {
            $viewCount = $item->view_count;
        }
        
        Log::info('InstagramScrapperService: Prepared data', [
            'comment' => $item->comment_count ?? 0,
            'view' => $viewCount,
            'like' => $item->like_count ?? 0
        ]);
        
        return [
            'comment' => $item->comment_count ?? 0,
            'view' => $viewCount,
            'like' => $item->like_count ?? 0,
            'upload_date' => $uploadDate
        ];
    }

    protected function getFinalUrl($url): string
    {
        try {
            // Perform the HTTP request and follow redirects automatically
            $response = Http::get($url);
            
            // Get the final redirected URL
            $finalUrl = $response->effectiveUri();
            
            Log::info('InstagramScrapperService: URL resolution', [
                'original' => $url,
                'final' => $finalUrl
            ]);
            
            return $finalUrl;
        } catch (\Exception $e) {
            Log::error('InstagramScrapperService: Error following URL redirect', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
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
            Log::info('InstagramScrapperService: Extracted reel shortcode', ['shortCode' => $matches[1]]);
            return $matches[1];
        } elseif (preg_match($postPattern, $link, $matches)) {
            Log::info('InstagramScrapperService: Extracted post shortcode', ['shortCode' => $matches[1]]);
            return $matches[1];
        }
        
        Log::error('InstagramScrapperService: Failed to extract shortcode', ['link' => $link]);
        return '';
    }
}