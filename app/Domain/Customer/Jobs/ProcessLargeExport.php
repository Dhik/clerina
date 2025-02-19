<?php

namespace App\Domain\Customer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Customer\Models\CustomersAnalysis;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLargeExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Increase timeout for large exports
    public $timeout = 3600; // 1 hour
    public $tries = 1; // Set to 1 for debugging

    protected $month;
    protected $status;
    protected $whichHp;
    protected $exportId;
    protected $userId;
    protected $recordsPerFile = 1500;
    
    /**
     * Create a new job instance.
     */
    public function __construct($month, $status, $whichHp, $exportId, $userId)
    {
        $this->month = $month;
        $this->status = $status;
        $this->whichHp = $whichHp;
        $this->exportId = $exportId;
        $this->userId = $userId;
        $this->onQueue('exports');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info("Starting export job: {$this->exportId}");
            
            // Get total count
            Log::info("Counting records for export: {$this->exportId}");
            $totalCount = $this->getRecordCount();
            Log::info("Found {$totalCount} records for export: {$this->exportId}");
            
            // Calculate number of files needed
            $fileCount = ceil($totalCount / $this->recordsPerFile);
            Log::info("Will create {$fileCount} files for export: {$this->exportId}");
            
            // Create temporary directory
            $tempDir = storage_path('app/temp_exports/' . $this->exportId);
            Log::info("Creating temp dir: {$tempDir}");
            
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0775, true)) {
                    Log::error("Failed to create temp directory: {$tempDir}");
                    throw new \Exception("Failed to create temp directory");
                }
                chmod($tempDir, 0775);
            }
            
            // Verify directory was created and has correct permissions
            if (!is_dir($tempDir) || !is_writable($tempDir)) {
                Log::error("Temp directory not writable: {$tempDir}");
                Log::error("Permissions: " . substr(sprintf('%o', fileperms($tempDir)), -4));
                Log::error("Owner: " . posix_getpwuid(fileowner($tempDir))['name']);
                throw new \Exception("Temp directory not writable");
            }
            
            Log::info("Starting file creation for export: {$this->exportId}");
            
            // Create multiple Excel files
            for ($i = 0; $i < $fileCount; $i++) {
                $offset = $i * $this->recordsPerFile;
                $filename = "customer_data_part_" . ($i + 1) . "_of_{$fileCount}.xlsx";
                $filePath = "{$tempDir}/{$filename}";
                
                Log::info("Processing chunk {$i}/{$fileCount} for export: {$this->exportId}");
                
                // Get data for this chunk
                Log::info("Getting data chunk at offset {$offset}");
                $data = $this->getChunkData($offset, $this->recordsPerFile);
                Log::info("Retrieved " . count($data) . " records for chunk {$i}");
                
                // Create Excel file
                Log::info("Creating Excel file: {$filePath}");
                $this->createExcelFile($data, $filePath);
                Log::info("Successfully created Excel file: {$filePath}");
                
                // Verify file was created
                if (!file_exists($filePath)) {
                    Log::error("Excel file was not created: {$filePath}");
                    throw new \Exception("Failed to create Excel file");
                }
                
                // Free up memory
                Log::info("Clearing memory for chunk {$i}");
                unset($data);
                gc_collect_cycles();
            }
            
            // Create ZIP archive
            $zipPath = storage_path('app/exports/' . $this->exportId . '.zip');
            Log::info("Creating ZIP archive: {$zipPath}");
            
            // Ensure exports directory exists and is writable
            $exportsDir = storage_path('app/exports');
            if (!file_exists($exportsDir)) {
                if (!mkdir($exportsDir, 0775, true)) {
                    Log::error("Failed to create exports directory: {$exportsDir}");
                    throw new \Exception("Failed to create exports directory");
                }
                chmod($exportsDir, 0775);
            }
            
            // Check if directory is writable
            if (!is_writable($exportsDir)) {
                Log::error("Exports directory not writable: {$exportsDir}");
                Log::error("Permissions: " . substr(sprintf('%o', fileperms($exportsDir)), -4));
                Log::error("Owner: " . posix_getpwuid(fileowner($exportsDir))['name']);
                throw new \Exception("Exports directory not writable");
            }
            
            $zipCreated = $this->createZipArchive($tempDir, $zipPath);
            
            if (!$zipCreated) {
                Log::error("Failed to create ZIP archive: {$zipPath}");
                throw new \Exception("Failed to create ZIP archive");
            }
            
            Log::info("Successfully created ZIP archive: {$zipPath}");
            
            // Verify ZIP file was created
            if (!file_exists($zipPath)) {
                Log::error("ZIP file was not created: {$zipPath}");
                throw new \Exception("ZIP file was not created");
            }
            
            // Clean up temp files
            Log::info("Cleaning up temp files: {$tempDir}");
            $this->cleanupTempFiles($tempDir);
            Log::info("Export job completed successfully: {$this->exportId}");
            
        } catch (\Exception $e) {
            Log::error("Export job failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Get record count
     */
    private function getRecordCount()
    {
        try {
            $query = CustomersAnalysis::query();
            
            if ($this->month) {
                $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
            }
            
            if ($this->status) {
                $query->where('status_customer', $this->status);
            }
            
            if ($this->whichHp) {
                $query->where('which_hp', $this->whichHp);
            }
            
            return $query->distinct('nomor_telepon')->count('nomor_telepon');
        } catch (\Exception $e) {
            Log::error("Error counting records: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get chunk data
     */
    private function getChunkData($offset, $limit)
    {
        try {
            $query = CustomersAnalysis::query();
            
            if ($this->month) {
                $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
            }
            
            if ($this->status) {
                $query->where('status_customer', $this->status);
            }
            
            if ($this->whichHp) {
                $query->where('which_hp', $this->whichHp);
            }
            
            return $query->select(
                    DB::raw('MIN(nama_penerima) as nama'),
                    'nomor_telepon as kontak',
                    DB::raw('NULL as email'),
                    DB::raw('MIN(alamat) as alamat'),
                    DB::raw('NULL as kecamatan'),
                    DB::raw('MIN(kota_kabupaten) as kota'),
                    DB::raw('MIN(provinsi) as provinsi'),
                    DB::raw('NULL as kode_pos'),
                    DB::raw('MIN(status_customer) as group_customer'),
                    DB::raw('MIN(which_hp) as hp_mana'),
                    DB::raw('NULL as note'),
                    DB::raw('NULL as user_terkait'),
                    DB::raw('NULL as birthday')
                )
                ->groupBy('nomor_telepon')
                ->skip($offset)
                ->take($limit)
                ->get();
        } catch (\Exception $e) {
            Log::error("Error getting chunk data: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create Excel file
     */
    private function createExcelFile($data, $filePath)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Add headers
            $headers = [
                'Nama', 'Kontak', 'Email', 'Alamat', 'Kecamatan', 'Kota', 
                'Provinsi', 'Kode Pos', 'Group', 'Tag', 'Note', 'User Terkait', 'Birthday'
            ];
            
            foreach ($headers as $index => $header) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
            }
            
            // Add data
            $row = 2;
            foreach ($data as $item) {
                $col = 1;
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nama);
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->kontak, DataType::TYPE_STRING);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->email);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->alamat);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->kecamatan);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->kota);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->provinsi);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->kode_pos);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->group_customer);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->hp_mana);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->note);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->user_terkait);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->birthday);
                $row++;
            }
            
            // Save file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            // Free memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error creating Excel file: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create ZIP archive
     */
    private function createZipArchive($sourceDir, $zipPath)
    {
        try {
            Log::info("Checking ZipArchive availability");
            if (!class_exists('ZipArchive')) {
                Log::error("ZipArchive class not found - PHP ZIP extension may not be installed");
                throw new \Exception("ZipArchive class not found");
            }
            
            $zip = new ZipArchive();
            
            Log::info("Opening ZIP file: {$zipPath}");
            $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($result !== true) {
                Log::error("Failed to open ZIP file, error code: {$result}");
                return false;
            }
            
            Log::info("Finding files to add to ZIP");
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            $fileCount = 0;
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $relativePath = basename($file->getRealPath());
                    $fullPath = $file->getRealPath();
                    
                    Log::info("Adding file to ZIP: {$fullPath} as {$relativePath}");
                    
                    if (!file_exists($fullPath)) {
                        Log::error("File does not exist: {$fullPath}");
                        continue;
                    }
                    
                    if (!is_readable($fullPath)) {
                        Log::error("File is not readable: {$fullPath}");
                        continue;
                    }
                    
                    $added = $zip->addFile($fullPath, $relativePath);
                    if (!$added) {
                        Log::error("Failed to add file to ZIP: {$fullPath}");
                    } else {
                        $fileCount++;
                    }
                }
            }
            
            Log::info("Added {$fileCount} files to ZIP");
            
            if ($fileCount === 0) {
                Log::error("No files were added to the ZIP archive");
                $zip->close();
                return false;
            }
            
            Log::info("Closing ZIP file");
            $closed = $zip->close();
            
            if (!$closed) {
                Log::error("Failed to close ZIP file");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error creating ZIP archive: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles($tempDir)
    {
        try {
            if (!file_exists($tempDir)) {
                Log::warning("Temp directory does not exist: {$tempDir}");
                return;
            }
            
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    Log::info("Removing directory: {$file->getRealPath()}");
                    if (!rmdir($file->getRealPath())) {
                        Log::warning("Failed to remove directory: {$file->getRealPath()}");
                    }
                } else {
                    Log::info("Removing file: {$file->getRealPath()}");
                    if (!unlink($file->getRealPath())) {
                        Log::warning("Failed to remove file: {$file->getRealPath()}");
                    }
                }
            }
            
            Log::info("Removing temp directory: {$tempDir}");
            if (!rmdir($tempDir)) {
                Log::warning("Failed to remove temp directory: {$tempDir}");
            }
        } catch (\Exception $e) {
            Log::error("Error cleaning up temp files: " . $e->getMessage());
            // Don't throw - continue with the job
        }
    }
}