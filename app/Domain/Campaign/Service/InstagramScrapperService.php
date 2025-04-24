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
        Log::info('InstagramScrapperService: Starting with link', ['link' => $link]);
        
        $finalUrl = $this->getFinalUrl($link);
        Log::info('InstagramScrapperService: Final URL after redirect', ['finalUrl' => $finalUrl]);
        
        $shortCode = $this->extractShortCode($finalUrl);
        Log::info('InstagramScrapperService: Extracted shortcode', ['shortCode' => $shortCode]);
        
        if (empty($shortCode)) {
            Log::error('Failed to extract shortcode from URL', ['link' => $link, 'finalUrl' => $finalUrl]);
            return null;
        }
        
        try {
            Log::info('InstagramScrapperService: Making API request', [
                'endpoint' => 'ig/post_info_v2/',
                'shortcode' => $shortCode
            ]);
            
            $response = $this->client->request('GET', 'ig/post_info_v2/', [
                'query' => [
                    'nocors' => 'false',
                    'shortcode' => $shortCode
                ],
            ]);
            
            $statusCode = $response->getStatusCode();
            Log::info('InstagramScrapperService: API response status', ['statusCode' => $statusCode]);
            
            $responseContent = $response->getBody()->getContents();
            Log::info('InstagramScrapperService: API response content length', ['length' => strlen($responseContent)]);
            
            $data = json_decode($responseContent);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('InstagramScrapperService: JSON decode error', ['error' => json_last_error_msg()]);
                return null;
            }
            
            // Check if we have valid items in the response
            if (!isset($data->items) || empty($data->items)) {
                Log::error('No items in response for IG post', ['shortCode' => $shortCode]);
                Log::info('Instagram API Response', ['data' => json_encode($data)]);
                return null;
            }
            
            $item = $data->items[0];
            
            // Extract the date from "taken_at" timestamp
            $uploadDate = null;
            if (isset($item->taken_at)) {
                // Convert Unix timestamp to Carbon date
                $uploadDate = Carbon::createFromTimestamp($item->taken_at)->toDateTimeString();
                Log::info('InstagramScrapperService: Found upload date', ['uploadDate' => $uploadDate]);
            }
            
            // More flexible approach to get view count
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
                Log::warning('InstagramScrapperService: No view count found in response');
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
            Log::error('Error in Instagram API request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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