<?php

namespace App\Domain\Sales\Services;

use GuzzleHttp\Client;

class TelegramService
{
    protected $client;
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        $this->client = new Client();
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function sendMessage($message)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $response = $this->client->post($url, [
            'form_params' => [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
