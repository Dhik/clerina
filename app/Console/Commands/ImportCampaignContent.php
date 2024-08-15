<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Campaign\Import\ContentImport;
use Illuminate\Support\Facades\Log;

class ImportCampaignContent extends Command
{
    protected $signature = 'campaign:import {file} {campaignId} {tenantId}';
    protected $description = 'Import campaign content from an Excel file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $file = $this->argument('file');
        $campaignId = $this->argument('campaignId');
        $tenantId = $this->argument('tenantId');

        // Get the correct path to the file in the storage
        $filePath = storage_path('app/public/' . $file);

        // Check if the file exists
        if (!file_exists($filePath)) {
            $this->error('File does not exist: ' . $filePath);
            return;
        }

        // Import the data from the Excel file
        try {
            $import = new ContentImport();
            Excel::import($import, $filePath);

            // Get imported data
            $importedData = $import->getImportedData();

            // Log or display the imported data
            $this->info('Imported Data Preview:');
            foreach ($importedData as $index => $row) {
                $this->info("Row $index: " . json_encode($row));
            }

            // Prompt the user to continue with the import
            if ($this->confirm('Do you wish to continue with storing the data in the database?')) {
                // Here you would normally call your BLL or service to save the data
                // For now, we'll just log a success message
                $this->info('Campaign content imported successfully.');
            } else {
                $this->info('Import cancelled.');
            }
        } catch (\Exception $e) {
            Log::error('Error importing campaign content: ' . $e->getMessage());
            $this->error('An error occurred while importing the data. Check the logs for more details.');
        }
    }
}
