<?php

// app/Console/Commands/ImportOrders.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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

        $import = new OrderImport($salesChannelId, $tenantId);
        Excel::import($import, $file);

        // Get the cleaned data
        // $cleanedData = $import->getCleanedData();

        // Display the cleaned data
        // $this->info('Cleaned Data Preview:');
        // foreach ($cleanedData as $index => $row) {
        //     $this->info("Row $index: " . json_encode($row));
        // }

        // Prompt the user to continue with the import
        if ($this->confirm('Do you wish to continue with storing the data in the database?')) {
            // Re-import the data to store it in the database
            Excel::import($import, $file);
            $this->info('Orders imported successfully.');
        } else {
            $this->info('Import cancelled.');
        }
    }
}