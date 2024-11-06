<?php

namespace App\Domain\Sales\Services;

use Google_Client;
use Google_Service_Sheets;

class GoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(storage_path('app/google-sheets-credentials.json'));
        $this->client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $this->client->useApplicationDefaultCredentials();

        $this->service = new Google_Service_Sheets($this->client);
        $this->spreadsheetId = '1ksZm0fLUTdZbf8ITNQXxOizbhpOfjHj32nWAthDFyWI';
    }

    public function getSheetData($range)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues();
    }
}