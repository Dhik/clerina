<?php

// app/Console/Commands/ImportOrders.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Order\Import\OrderImportLazada;
use App\Domain\Order\Import\OrderImport;

class ImportOrders extends Command
{
    protected $signature = 'orders:import {file} {salesChannelId} {tenantId}';
    protected $description = 'Import orders from an Excel file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $file = $this->argument('file');
        $salesChannelId = $this->argument('salesChannelId');
        $tenantId = $this->argument('tenantId');

        // Get the correct path to the file in the storage
        $filePath = storage_path('app/public/' . $file);

        // Check if the file exists
        if (!file_exists($filePath)) {
            $this->error('File does not exist: ' . $filePath);
            return;
        }

        $import = new OrderImportLazada($salesChannelId, $tenantId);
        Excel::import($import, $filePath);

        // Get the cleaned data
        $cleanedData = $import->getCleanedData();

        // Display the cleaned data
        $this->info('Cleaned Data Preview:');
        foreach ($cleanedData as $index => $row) {
            $this->info("Row $index: " . json_encode($row));
        }

        // Prompt the user to continue with the import
        if ($this->confirm('Do you wish to continue with storing the data in the database?')) {
            // Re-import the data to store it in the database
            Excel::import($import, $filePath);
            $this->info('Orders imported successfully.');
        } else {
            $this->info('Import cancelled.');
        }
    }
}
