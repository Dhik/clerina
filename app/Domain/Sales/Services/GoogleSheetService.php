<?php

namespace App\Domain\Sales\Services;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

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

     /**
     * Export data to a specified range in Google Sheets
     * 
     * @param string $range The range to update (e.g. 'Sheet1!A1:D10')
     * @param array $data The data to export (2D array)
     * @param string $valueInputOption How to interpret the data (RAW or USER_ENTERED)
     * @return void
     */
    public function exportData($range, array $data, $valueInputOption = 'RAW')
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $data
        ]);

        $params = [
            'valueInputOption' => $valueInputOption
        ];

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
    }

    /**
     * Append data to a specified range in Google Sheets
     * 
     * @param string $range The range to append after (e.g. 'Sheet1!A:D')
     * @param array $data The data to append (2D array)
     * @param string $valueInputOption How to interpret the data (RAW or USER_ENTERED)
     * @return void
     */
    public function appendData($range, array $data, $valueInputOption = 'RAW')
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $data
        ]);

        $params = [
            'valueInputOption' => $valueInputOption
        ];

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
    }

    /**
     * Clear a specified range in Google Sheets
     * 
     * @param string $range The range to clear (e.g. 'Sheet1!A1:D10')
     * @return void
     */
    public function clearRange($range)
    {
        $this->service->spreadsheets_values->clear(
            $this->spreadsheetId,
            $range,
            new \Google_Service_Sheets_ClearValuesRequest()
        );
    }
}