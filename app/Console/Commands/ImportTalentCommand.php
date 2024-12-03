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
        $file = $this->argument('file');
        $filePath = storage_path('app/public/' . $file);
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1; 
        }

        try {
            Excel::import(new TalentImport, $filePath);

            $this->info('Talent data imported successfully.');

            return 0; 
        } catch (\Exception $e) {
            $this->error('Error importing data: ' . $e->getMessage());
            return 1;
        }
    }
}
