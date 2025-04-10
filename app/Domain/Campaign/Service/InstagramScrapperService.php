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
            'timeout' => 30, // Increased timeout for slower responses
            'connect_timeout' => 10,
        ]);
    }

    public function getPostInfo($link): ?array
    {
        // Add small random delay to avoid pattern detection by API
        usleep(rand(500000, 1500000)); // Sleep for 0.5-1.5 seconds
        
        try {
            $finalUrl = $this->getFinalUrl($link);
            $shortCode = $this->extractShortCode($finalUrl);
            
            if (empty($shortCode)) {
                Log::error('Failed to extract shortcode from URL: ' . $link);
                return null;
            }
            
            // Log request tracking
            Log::debug('Sending Instagram API request for shortcode: ' . $shortCode);
            
            $response = $this->client->request('GET', 'ig/post_info_v2/', [
                'query' => [
                    'nocors' => 'false',
                    'shortcode' => $shortCode
                ],
            ]);

            $statusCode = $response->getStatusCode();
            Log::debug('Instagram API response status: ' . $statusCode);
            
            $responseContent = $response->getBody()->getContents();
            $data = json_decode($responseContent);
            
            // Check if we have valid items in the response
            if (!isset($data->items) || empty($data->items)) {
                Log::error('No items in response for IG post: ' . $shortCode);
                Log::debug('API Response: ' . substr($responseContent, 0, 500) . '...');
                return null;
            }
            
            $item = $data->items[0];
            
            // Extract the date from "taken_at" timestamp
            $uploadDate = null;
            if (isset($item->taken_at)) {
                // Convert Unix timestamp to Carbon date
                $uploadDate = Carbon::createFromTimestamp($item->taken_at)->toDateTimeString();
            }
            
            $result = [
                'comment' => $item->comment_count ?? 0,
                'view' => $item->ig_play_count ?? 0,
                'like' => $item->like_count ?? 0,
                'upload_date' => $uploadDate
            ];
            
            Log::debug('Successfully retrieved Instagram data for shortcode: ' . $shortCode);
            
            return $result;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle specific HTTP errors
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'unknown';
            $errorMessage = "HTTP error {$statusCode} fetching IG info for {$link}: " . $e->getMessage();
            
            Log::error($errorMessage);
            
            // Handle rate limiting specifically
            if ($statusCode === 429) {
                Log::warning('Instagram API rate limit reached. Consider increasing delay between requests.');
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching IG info: ' . $e->getMessage() . ' for link: ' . $link);
            return null;
        }
    }

    protected function getFinalUrl($url): string
    {
        try {
            // Add a small delay before following redirects
            usleep(rand(200000, 500000)); // 0.2-0.5 seconds
            
            // Perform the HTTP request and follow redirects automatically
            $response = Http::timeout(15)->get($url);

            // Get the final redirected URL
            $finalUrl = $response->effectiveUri();
            
            Log::debug('Followed redirect from ' . $url . ' to ' . $finalUrl);

            return $finalUrl;
        } catch (\Exception $e) {
            Log::error('Error following URL redirect: ' . $e->getMessage() . ' for URL: ' . $url);
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

        Log::warning('Could not extract shortcode from Instagram link: ' . $link);
        return '';
    }
}