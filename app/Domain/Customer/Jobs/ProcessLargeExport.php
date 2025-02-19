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

    // Set higher timeout and attempts for large exports
    public $timeout = 3600; // 1 hour
    public $tries = 2;

    protected $month;
    protected $status;
    protected $whichHp;
    protected $exportId;
    protected $userId;
    protected $recordsPerFile = 1500;
    
    /**
     * Create a new job instance.
     *
     * @return void
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
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get total count
            $totalCount = $this->getRecordCount();
            
            // Calculate number of files needed
            $fileCount = ceil($totalCount / $this->recordsPerFile);
            
            // Create temporary directory
            $tempDir = storage_path('app/temp_exports/' . $this->exportId);
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Create multiple Excel files
            for ($i = 0; $i < $fileCount; $i++) {
                $offset = $i * $this->recordsPerFile;
                $filename = "customer_data_part_" . ($i + 1) . "_of_{$fileCount}.xlsx";
                $filePath = "{$tempDir}/{$filename}";
                
                // Get data for this chunk
                $data = $this->getChunkData($offset, $this->recordsPerFile);
                
                // Create Excel file
                $this->createExcelFile($data, $filePath);
                
                // Free up memory
                unset($data);
                gc_collect_cycles();
            }
            
            // Create ZIP archive
            $zipPath = storage_path('app/exports/' . $this->exportId . '.zip');
            $this->createZipArchive($tempDir, $zipPath);
            
            // Clean up temp files
            $this->cleanupTempFiles($tempDir);
            
        } catch (\Exception $e) {
            Log::error('Export job failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Get record count
     */
    private function getRecordCount()
    {
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
    }
    
    /**
     * Get chunk data
     */
    private function getChunkData($offset, $limit)
    {
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
    }
    
    /**
     * Create Excel file
     */
    private function createExcelFile($data, $filePath)
    {
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
    }
    
    /**
     * Create ZIP archive
     */
    private function createZipArchive($sourceDir, $zipPath)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $relativePath = basename($file->getRealPath());
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }
            
            $zip->close();
            return true;
        }
        
        return false;
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles($tempDir)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($tempDir);
    }
}