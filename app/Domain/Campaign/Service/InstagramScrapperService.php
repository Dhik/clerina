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
            Log::error('INSTAGRAM_TEST_MARKER: Starting to process ' . $link);
            
            // Try to extract shortcode directly from the original link first
            $shortCode = $this->extractShortCode($link);
            
            // If that fails, try following redirects
            if (empty($shortCode)) {
                $finalUrl = $this->getFinalUrl($link);
                Log::info('InstagramScrapperService: URL resolution', [
                    'original' => $link,
                    'final' => $finalUrl
                ]);
                
                $shortCode = $this->extractShortCode($finalUrl);
                
                if (empty($shortCode)) {
                    Log::error('InstagramScrapperService: Failed to extract shortcode', ['link' => $finalUrl]);
                    Log::error('InstagramScrapperService: Failed to extract shortcode from URL', ['link' => $link]);
                    return null;
                }
            }
            
            Log::info('InstagramScrapperService: Using shortcode', ['shortCode' => $shortCode]);
            
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
                Log::error('InstagramScrapperService: No items in response', [
                    'shortCode' => $shortCode,
                    'response' => substr($content, 0, 500) . '...' // Log just a portion to avoid huge logs
                ]);
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
                Log::info('InstagramScrapperService: Using play_count', ['viewCount' => $viewCount]);
            } elseif (isset($item->ig_play_count)) {
                $viewCount = $item->ig_play_count;
                Log::info('InstagramScrapperService: Using ig_play_count', ['viewCount' => $viewCount]);
            } elseif (isset($item->view_count)) {
                $viewCount = $item->view_count;
                Log::info('InstagramScrapperService: Using view_count', ['viewCount' => $viewCount]);
            } else {
                Log::warning('InstagramScrapperService: No view count field found');
            }
            
            $result = [
                'comment' => $item->comment_count ?? 0,
                'view' => $viewCount,
                'like' => $item->like_count ?? 0,
                'upload_date' => $uploadDate
            ];
            
            Log::info('InstagramScrapperService: Successfully extracted data', $result);
            
            return $result;
            
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
        
        // First try direct pattern match
        if (preg_match($reelPattern, $link, $matches)) {
            Log::info('InstagramScrapperService: Extracted reel shortcode directly', ['shortCode' => $matches[1]]);
            return $matches[1];
        } elseif (preg_match($postPattern, $link, $matches)) {
            Log::info('InstagramScrapperService: Extracted post shortcode directly', ['shortCode' => $matches[1]]);
            return $matches[1];
        }
        
        // If redirected to login page, look for the ID in the "next" parameter
        if (strpos($link, 'accounts/login') !== false && strpos($link, 'next=') !== false) {
            $urlParts = parse_url($link);
            if (isset($urlParts['query'])) {
                parse_str($urlParts['query'], $query);
                if (isset($query['next'])) {
                    // URL decode the next parameter
                    $nextUrl = urldecode($query['next']);
                    Log::info('InstagramScrapperService: Checking next URL', ['nextUrl' => $nextUrl]);
                    
                    // Try to extract the shortcode from the next URL
                    if (preg_match($reelPattern, $nextUrl, $matches)) {
                        Log::info('InstagramScrapperService: Extracted reel shortcode from next parameter', ['shortCode' => $matches[1]]);
                        return $matches[1];
                    } elseif (preg_match($postPattern, $nextUrl, $matches)) {
                        Log::info('InstagramScrapperService: Extracted post shortcode from next parameter', ['shortCode' => $matches[1]]);
                        return $matches[1];
                    }
                }
            }
        }
        
        // As a last resort, just try to find the ID pattern anywhere in the URL
        // This handles cases where the URL structure might be unexpected
        if (preg_match('/reel\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
            Log::info('InstagramScrapperService: Extracted reel shortcode using fallback pattern', ['shortCode' => $matches[1]]);
            return $matches[1];
        } elseif (preg_match('/p\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
            Log::info('InstagramScrapperService: Extracted post shortcode using fallback pattern', ['shortCode' => $matches[1]]);
            return $matches[1];
        }
        
        Log::error('InstagramScrapperService: Failed to extract shortcode with all methods', ['link' => $link]);
        return '';
    }
}