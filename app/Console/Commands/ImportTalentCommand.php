<?php

namespace App\Console\Commands;
use App\Domain\Talent\Import\TalentImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Console\Command;

class ImportTalentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'talent:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the file path from the argument
        $file = $this->argument('file');
        $filePath = storage_path('app/public/' . $file);

        // Ensure the file exists before trying to import
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1; // Return error code
        }

        try {
            // Import the data using the TalentImport class
            Excel::import(new TalentImport, $filePath);

            $this->info('Talent data imported successfully.');

            return 0; // Success
        } catch (\Exception $e) {
            // If something goes wrong, catch the exception and show the error
            $this->error('Error importing data: ' . $e->getMessage());
            return 1; // Return error code
        }
    }
}
