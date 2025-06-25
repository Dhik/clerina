<?php

namespace App\Domain\BCGMetrics\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\BCGMetrics\BLL\BCGMetrics\BCGMetricsBLLInterface;
use App\Domain\BCGMetrics\Models\BCGMetrics;
use App\Domain\BCGMetrics\Requests\BCGMetricsRequest;
use App\Domain\Sales\Services\GoogleSheetService;
use App\Domain\BCGMetrics\Models\BCGProduct;

/**
 * @property BCGMetricsBLLInterface bCGMetricsBLL
 */
class BCGMetricsController extends Controller
{
    protected $googleSheetService;
    public function __construct(
        GoogleSheetService $googleSheetService
        )
    {
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BCGMetricsRequest $request
     */
    public function store(BCGMetricsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param BCGMetrics $bCGMetrics
     */
    public function show(BCGMetrics $bCGMetrics)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  BCGMetrics  $bCGMetrics
     */
    public function edit(BCGMetrics $bCGMetrics)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BCGMetricsRequest $request
     * @param  BCGMetrics  $bCGMetrics
     */
    public function update(BCGMetricsRequest $request, BCGMetrics $bCGMetrics)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BCGMetrics $bCGMetrics
     */
    public function destroy(BCGMetrics $bCGMetrics)
    {
        //
    }
    public function importBcgProduct()
    {
        $this->googleSheetService->setSpreadsheetId('1MnY6beeJjZIJ_lMWytdPb6shLlX7gkselbynkRfELbE');
        $range = 'DATA PRODUCT!A2:X'; // Assuming data starts from row 2
        $sheetData = $this->googleSheetService->getSheetData($range);
        
        $tenant_id = 1; // As specified in your requirements
        $date = '2025-05-01'; // As specified
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        
        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Skip if essential data is missing (kode_produk and nama_produk)
                if (empty($row[0]) || empty($row[1])) {
                    $skippedRows++;
                    continue;
                }
                
                // Extract data from specific columns
                $kode_produk = $row[0] ?? null; // Column A
                $nama_produk = $row[1] ?? null; // Column B
                $sku = $row[7] ?? null; // Column H
                
                // Handle formatted numbers with dot separators
                $visitor = null;
                if (isset($row[8]) && !empty($row[8])) {
                    $cleanedVisitor = str_replace('.', '', trim($row[8]));
                    $visitor = is_numeric($cleanedVisitor) ? (int)$cleanedVisitor : null;
                }
                
                $jumlah_atc = null;
                if (isset($row[14]) && !empty($row[14])) {
                    $cleanedAtc = str_replace('.', '', trim($row[14]));
                    $jumlah_atc = is_numeric($cleanedAtc) ? (int)$cleanedAtc : null;
                }
                
                $jumlah_pembeli = null;
                if (isset($row[21]) && !empty($row[21])) {
                    $cleanedPembeli = str_replace('.', '', trim($row[21]));
                    $jumlah_pembeli = is_numeric($cleanedPembeli) ? (int)$cleanedPembeli : null;
                }
                
                $qty_sold = null;
                if (isset($row[22]) && !empty($row[22])) {
                    $cleanedQty = str_replace('.', '', trim($row[22]));
                    $qty_sold = is_numeric($cleanedQty) ? (int)$cleanedQty : null;
                }
                
                // Handle formatted numbers with dot separators for sales (Column X)
                $sales = null;
                if (isset($row[23]) && !empty($row[23])) {
                    $cleanedSales = str_replace('.', '', trim($row[23])); // Remove dots
                    $sales = is_numeric($cleanedSales) ? (int)$cleanedSales : null;
                }
                
                // Check for duplicates based on date, tenant_id, and kode_produk
                $existingProduct = BcgProduct::where('date', $date)
                                        ->where('tenant_id', $tenant_id)
                                        ->where('kode_produk', $kode_produk)
                                        ->first();
                
                if ($existingProduct) {
                    $duplicateRows++;
                    continue;
                }
                
                $productData = [
                    'date' => $date,
                    'tenant_id' => $tenant_id,
                    'kode_produk' => $kode_produk,
                    'nama_produk' => $nama_produk,
                    'sku' => $sku,
                    'visitor' => $visitor,
                    'jumlah_atc' => $jumlah_atc,
                    'jumlah_pembeli' => $jumlah_pembeli,
                    'qty_sold' => $qty_sold,
                    'sales' => $sales,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Create new BCG product record
                BcgProduct::create($productData);
                $processedRows++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }
        
        return response()->json([
            'message' => 'BCG Product data imported successfully',
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows
        ]);
    }
    public function importBcgStock()
    {
        $this->googleSheetService->setSpreadsheetId('1MnY6beeJjZIJ_lMWytdPb6shLlX7gkselbynkRfELbE');
        $range = 'DATA STOCK!A2:H'; // Assuming data starts from row 2
        $sheetData = $this->googleSheetService->getSheetData($range);
        
        $date = '2025-05-01'; // Same date as used in product import
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $notFoundRows = 0;
        
        // First, collect all data and group by kode_produk to calculate averages
        $groupedData = [];
        
        foreach ($sheetData as $row) {
            // Skip if kode_produk is missing
            if (empty($row[0])) {
                $skippedRows++;
                continue;
            }
            
            $kode_produk = trim($row[0]); // Column A
            
            // Handle formatted numbers for harga (Column G) and stock (Column H)
            $harga = null;
            if (isset($row[6]) && !empty($row[6])) {
                $cleanedHarga = str_replace('.', '', trim($row[6]));
                $harga = is_numeric($cleanedHarga) ? (int)$cleanedHarga : null;
            }
            
            $stock = null;
            if (isset($row[7]) && !empty($row[7])) {
                $cleanedStock = str_replace('.', '', trim($row[7]));
                $stock = is_numeric($cleanedStock) ? (int)$cleanedStock : null;
            }
            
            // Group data by kode_produk
            if (!isset($groupedData[$kode_produk])) {
                $groupedData[$kode_produk] = [
                    'harga_values' => [],
                    'stock_values' => []
                ];
            }
            
            // Add values to arrays (only if not null)
            if ($harga !== null) {
                $groupedData[$kode_produk]['harga_values'][] = $harga;
            }
            if ($stock !== null) {
                $groupedData[$kode_produk]['stock_values'][] = $stock;
            }
        }
        
        // Now process the grouped data and calculate averages
        foreach ($groupedData as $kode_produk => $data) {
            // Calculate averages
            $avg_harga = !empty($data['harga_values']) ? 
                round(array_sum($data['harga_values']) / count($data['harga_values'])) : null;
                
            $avg_stock = !empty($data['stock_values']) ? 
                round(array_sum($data['stock_values']) / count($data['stock_values'])) : null;
            
            // Find existing product by kode_produk AND date
            $existingProduct = BcgProduct::where('kode_produk', $kode_produk)
                                        ->where('date', $date)
                                        ->first();
            
            if (!$existingProduct) {
                $notFoundRows++;
                continue; // Skip if product doesn't exist for this date
            }
            
            // Update the existing product with averaged stock and harga
            $existingProduct->update([
                'stock' => $avg_stock,
                'harga' => $avg_harga,
                'updated_at' => now(),
            ]);
            
            $processedRows++;
        }
        
        return response()->json([
            'message' => 'BCG Stock data imported successfully with averages calculated',
            'total_rows' => $totalRows,
            'unique_products' => count($groupedData),
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'not_found_rows' => $notFoundRows
        ]);
    }
}
