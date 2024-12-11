<?php

namespace App\Console\Commands;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Console\Command;
use App\Domain\Product\Import\ProductImport;

class ImportProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from an Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $filePath = storage_path('app/public/' . $file);
        
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        try {
            Excel::import(new ProductImport, $filePath);
            $this->info('Products imported successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error importing products: ' . $e->getMessage());
            return 1;
        }
    }
}
