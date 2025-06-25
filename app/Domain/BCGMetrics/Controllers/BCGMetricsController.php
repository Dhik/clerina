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
        $date = '2025-06-01'; // As specified
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
                $visitor = isset($row[8]) && is_numeric($row[8]) ? (int)$row[8] : null; // Column I
                $jumlah_atc = isset($row[14]) && is_numeric($row[14]) ? (int)$row[14] : null; // Column O
                $jumlah_pembeli = isset($row[21]) && is_numeric($row[21]) ? (int)$row[21] : null; // Column V
                $qty_sold = isset($row[22]) && is_numeric($row[22]) ? (int)$row[22] : null; // Column W
                $sales = isset($row[23]) && is_numeric($row[23]) ? (int)$row[23] : null; // Column X
                
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
}
