<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Employee\Import\PayrollImport;

class ImportPayrolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payrolls:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import payrolls from an Excel file';


    public function __construct() {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $filePath = storage_path('app/public/'.$file);
        if (!file_exists($filePath)) {
            $this->error('File does not exist: '.$filePath);
            return;
        }

        try {
            Excel::import(new PayrollImport, $filePath);
            $this->info('Payrolls imported successfully.');
        } catch (\Exception $e) {
            $this->error('Error importing payrolls: '. $e->getMessage());
        }
    }
}
