<?php
namespace App\Domain\Customer\Exports;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Domain\Customer\Models\CustomersAnalysis;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use App\Domain\Customer\Notifications\ExportCompleted;

class ChunkedExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;
    protected $status;
    protected $whichHp;
    protected $userId;
    protected $recordsPerFile = 1500; // Maximum records per Excel file
    
    public function __construct($month = null, $status = null, $whichHp = null, $userId = null)
    {
        $this->month = $month;
        $this->status = $status;
        $this->whichHp = $whichHp;
        $this->userId = $userId;
    }
    
    public function handle()
    {
        // Get total count of records
        $totalCount = $this->getRecordCount();
        
        // Calculate number of files needed
        $fileCount = ceil($totalCount / $this->recordsPerFile);
        
        // Generate timestamp for unique file naming
        $timestamp = now()->format('YmdHis');
        $exportId = uniqid();
        $zipFilename = "customer_export_{$exportId}.zip";
        $zipPath = "exports/{$zipFilename}";
        
        // Create temporary directory
        $tempDir = storage_path('app/temp_exports/' . $exportId);
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Create multiple Excel files
        for ($i = 0; $i < $fileCount; $i++) {
            $offset = $i * $this->recordsPerFile;
            $filename = "customer_data_part_" . ($i + 1) . "_of_{$fileCount}.xlsx";
            $filePath = "{$tempDir}/{$filename}";
            
            // Generate Excel file for this chunk
            $this->exportChunk($offset, $filePath);
        }
        
        // Create ZIP archive
        $zip = new ZipArchive();
        $zipFullPath = storage_path('app/' . $zipPath);
        
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $relativePath = basename($file->getRealPath());
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }
            
            $zip->close();
            
            // Clean up temporary files
            $this->cleanupTempFiles($tempDir);
            
            // Notify user
            $user = \App\Models\User::find($this->userId);
            if ($user) {
                $user->notify(new ExportCompleted($zipFilename));
            }
        }
    }
    
    protected function exportChunk($offset, $filePath)
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
        
        $query = $query->select(
                DB::raw('MIN(nama_penerima) as nama'),
                'nomor_telepon as kontak',
                DB::raw('NULL as email'),
                DB::raw('MIN(alamat) as alamat'),
                DB::raw('NULL as kecamatan'),
                DB::raw('MIN(kota_kabupaten) as kota'),
                DB::raw('MIN(provinsi) as provinsi'),
                DB::raw('NULL as kode_pos'),
                DB::raw('MIN(status_customer) as `group_customer`'),
                DB::raw('MIN(which_hp) as hp_mana'),
                DB::raw('NULL as note'),
                DB::raw('NULL as user_terkait'),
                DB::raw('NULL as birthday')
            )
            ->groupBy('nomor_telepon')
            ->skip($offset)
            ->take($this->recordsPerFile);
            
        $data = $query->get();
        
        // Create Excel file for this chunk
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->createSpreadsheet($data));
        $writer->save($filePath);
    }
    
    protected function createSpreadsheet($data)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->kontak, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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
        
        return $spreadsheet;
    }
    
    protected function getRecordCount()
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
    
    protected function cleanupTempFiles($tempDir)
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