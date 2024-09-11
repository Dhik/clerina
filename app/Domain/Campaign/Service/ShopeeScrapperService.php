<?php

namespace App\Domain\Campaign\Service;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ShopeeScrapperService {
    protected Client $client;

    public function __construct() {
        $this->client = new Client([
            'base_uri' => 'https://shopee.co.id/',
        ]);
    }
    public function getData(string $videoUrl): ?array
    {
        $data = $this->getInfoByURL($videoUrl);
        return $this->prepareData($data);
    }
    protected function prepareData($htmlContent): ?array {
        $data = $this->extractJsonData($htmlContent);
        if (empty($data) || empty($data['props']['pageProps']['timelineVideo']['list'][0]['meta']['countInfo'])) {
            return null;
        }
        $videoData = $data['props']['pageProps']['timelineVideo']['list'][0]['meta'];
        $countInfo = $videoData['countInfo'];

        $uploadTimestamp = $videoData['ctime'] / 1000;
        $uploadDate = Carbon::createFromTimestampUTC($uploadTimestamp)->format('Y-m-d H:i:s');

        return [
            'view' => $countInfo['views'] ?? 0,
            'like' => $countInfo['likes'] ?? 0,
            'comment' => $countInfo['comments'] ?? 0,
            'upload_date' => $uploadDate,
        ];
    }
    protected function getInfoByURL(string $videoUrl): ?string 
    {
        try{
            $response = $this->client->request('GET', $videoUrl);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            Log::error('Error fetching video data from Shopee: '. $e);
            return null;
        }
    }
    protected function extractJsonData(string $htmlContent): ?array
    {
        preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $htmlContent, $matches);
        if (!empty($matches[1])) {
            return json_decode($matches[1], true);
        }
        return null;
    }
}