<?php

namespace App\Domain\BCGMetrics\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\BCGMetrics\BLL\BCGMetrics\BCGMetricsBLLInterface;
use App\Domain\BCGMetrics\Models\BCGMetrics;
use App\Domain\BCGMetrics\Requests\BCGMetricsRequest;
use App\Domain\Sales\Services\GoogleSheetService;
use App\Domain\BCGMetrics\Models\BCGProduct;
use App\Domain\BCGMetrics\Services\BCGAnalysisService;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\BCGMetrics\Exports\BCGAnalysisExport;

/**
 * @property BCGMetricsBLLInterface bCGMetricsBLL
 */
class BCGMetricsController extends Controller
{
    protected $googleSheetService;
    protected $bcgAnalysisService;

    public function __construct(
        GoogleSheetService $googleSheetService,
        BCGAnalysisService $bcgAnalysisService
    ) {
        $this->googleSheetService = $googleSheetService;
        $this->bcgAnalysisService = $bcgAnalysisService;
    }

    public function index()
{
    // Get products with complete data for analysis
    $products = BCGProduct::whereNotNull('visitor')
        ->whereNotNull('jumlah_pembeli')
        ->whereNotNull('harga')
        ->where('visitor', '>', 0)
        ->where('date', '2025-05-01')
        ->get();

    $medianTraffic = $products->median('visitor');
    
    $processedProducts = $products->map(function ($product) use ($medianTraffic) {
        $conversionRate = ($product->jumlah_pembeli / $product->visitor) * 100;
        $benchmarkConversion = $this->getBenchmarkConversion($product->harga);
        
        $isHighTraffic = $product->visitor >= $medianTraffic;
        $isHighConversion = $conversionRate >= $benchmarkConversion;
        
        if ($isHighTraffic && $isHighConversion) {
            $quadrant = 'Stars';
            $quadrantColor = '#28a745';
        } elseif ($isHighTraffic && !$isHighConversion) {
            $quadrant = 'Question Marks';
            $quadrantColor = '#17a2b8';
        } elseif (!$isHighTraffic && $isHighConversion) {
            $quadrant = 'Cash Cows';
            $quadrantColor = '#ffc107';
        } else {
            $quadrant = 'Dogs';
            $quadrantColor = '#dc3545';
        }

        return [
            'kode_produk' => $product->kode_produk,
            'nama_produk' => $product->nama_produk,
            'sku' => $product->sku,
            'visitor' => $product->visitor,
            'jumlah_pembeli' => $product->jumlah_pembeli,
            'qty_sold' => $product->qty_sold ?? 0, // ADD THIS LINE
            'conversion_rate' => round($conversionRate, 2),
            'benchmark_conversion' => $benchmarkConversion,
            'harga' => $product->harga,
            'sales' => $product->sales ?? 0,
            'stock' => $product->stock ?? 0,
            'biaya_ads' => $product->biaya_ads ?? 0,
            'omset_penjualan' => $product->omset_penjualan ?? 0,
            'quadrant' => $quadrant,
            'quadrant_color' => $quadrantColor,
            'roas' => $product->biaya_ads > 0 ? round(($product->omset_penjualan ?? 0) / $product->biaya_ads, 2) : 0
        ];
    });

    $quadrantSummary = $processedProducts->groupBy('quadrant')->map(function ($group, $quadrant) {
        return [
            'quadrant' => $quadrant,
            'count' => $group->count(),
            'total_revenue' => $group->sum('sales'),
            'total_ads_cost' => $group->sum('biaya_ads'),
            'avg_conversion' => round($group->avg('conversion_rate'), 2),
            'avg_traffic' => round($group->avg('visitor'), 0),
            'total_stock' => $group->sum('stock'),
            'avg_roas' => round($group->avg('roas'), 2)
        ];
    });

    return view('admin.bcg_metrics.index', compact('processedProducts', 'quadrantSummary', 'medianTraffic'));
}

    /**
     * Get chart data for scatter plot
     */
    public function getChartData()
    {
        $products = BCGProduct::whereNotNull('visitor')
            ->whereNotNull('jumlah_pembeli')
            ->whereNotNull('harga')
            ->where('visitor', '>', 0)
            ->where('date', '2025-05-01')
            ->get();

        $medianTraffic = $products->median('visitor');
        
        $chartData = $products->map(function ($product) use ($medianTraffic) {
            $conversionRate = ($product->jumlah_pembeli / $product->visitor) * 100;
            $benchmarkConversion = $this->getBenchmarkConversion($product->harga);
            
            $isHighTraffic = $product->visitor >= $medianTraffic;
            $isHighConversion = $conversionRate >= $benchmarkConversion;
            
            if ($isHighTraffic && $isHighConversion) {
                $quadrant = 'Stars';
                $color = '#28a745';
            } elseif ($isHighTraffic && !$isHighConversion) {
                $quadrant = 'Question Marks';
                $color = '#17a2b8';
            } elseif (!$isHighTraffic && $isHighConversion) {
                $quadrant = 'Cash Cows';
                $color = '#ffc107';
            } else {
                $quadrant = 'Dogs';
                $color = '#dc3545';
            }

            return [
                'x' => $product->visitor,
                'y' => round($conversionRate, 2),
                'r' => min(max(($product->sales ?? 0) / 10000000, 5), 50),
                'label' => $product->sku ?: $product->kode_produk,
                'quadrant' => $quadrant,
                'color' => $color,
                'revenue' => $product->sales ?? 0,
                'benchmark' => $benchmarkConversion
            ];
        })->filter(function ($item) {
            return $item['y'] <= 80; // Exclude where y > 80
        })->values();

        return response()->json([
            'data' => $chartData,
            'medianTraffic' => $medianTraffic,
            'benchmarks' => [
                'traffic' => $medianTraffic,
                'conversion' => 1.0
            ]
        ]);
    }

    /**
     * Get benchmark conversion rate based on price
     */
    private function getBenchmarkConversion($price)
    {
        if ($price < 75000) return 2.0;
        if ($price < 100000) return 1.5;
        if ($price < 125000) return 1.0;
        if ($price < 150000) return 0.8;
        return 0.6;
    }

    /**
     * Get detailed analysis for specific quadrant
     */
    public function getQuadrantDetails($quadrant)
    {
        $products = BCGProduct::whereNotNull('visitor')
            ->whereNotNull('jumlah_pembeli')
            ->whereNotNull('harga')
            ->where('visitor', '>', 0)
            ->where('date', '2025-05-01')
            ->get();

        $medianTraffic = $products->median('visitor');
        
        $filteredProducts = $products->filter(function ($product) use ($medianTraffic, $quadrant) {
            $conversionRate = ($product->jumlah_pembeli / $product->visitor) * 100;
            $benchmarkConversion = $this->getBenchmarkConversion($product->harga);
            
            $isHighTraffic = $product->visitor >= $medianTraffic;
            $isHighConversion = $conversionRate >= $benchmarkConversion;
            
            $productQuadrant = '';
            if ($isHighTraffic && $isHighConversion) {
                $productQuadrant = 'Stars';
            } elseif ($isHighTraffic && !$isHighConversion) {
                $productQuadrant = 'Question Marks';
            } elseif (!$isHighTraffic && $isHighConversion) {
                $productQuadrant = 'Cash Cows';
            } else {
                $productQuadrant = 'Dogs';
            }
            
            return $productQuadrant === $quadrant;
        })->values();

        return response()->json($filteredProducts);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    public function getAnalysisDashboard($date = '2025-05-01')
    {
        $analysis = $this->bcgAnalysisService->generateAnalysis($date);
        
        return view('bcg_metrics.analysis', compact('analysis', 'date'));
    }

    /**
     * Get recommendations API endpoint
     */
    public function getRecommendations($date = '2025-05-01')
    {
        $analysis = $this->bcgAnalysisService->generateAnalysis($date);
        
        return response()->json([
            'recommendations' => $analysis['recommendations'],
            'opportunities' => $analysis['opportunities'],
            'risks' => $analysis['risk_products']
        ]);
    }

    /**
     * Export analysis to Excel
     */
    public function exportAnalysis($format = 'excel', $date = '2025-05-01')
    {
        $analysis = $this->bcgAnalysisService->generateAnalysis($date);
        
        switch ($format) {
            case 'excel':
                return Excel::download(new BCGAnalysisExport($analysis), 'bcg-analysis-' . $date . '.xlsx');
            case 'csv':
                $csvContent = $this->bcgAnalysisService->exportAnalysis('csv', $date);
                return response($csvContent)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="bcg-analysis-' . $date . '.csv"');
            case 'json':
                $jsonContent = $this->bcgAnalysisService->exportAnalysis('json', $date);
                return response($jsonContent)
                    ->header('Content-Type', 'application/json')
                    ->header('Content-Disposition', 'attachment; filename="bcg-analysis-' . $date . '.json"');
            default:
                return response()->json(['error' => 'Unsupported format'], 400);
        }
    }

    /**
     * Get performance trends (if you have historical data)
     */
    public function getPerformanceTrends()
    {
        // Example for future implementation with multiple dates
        $dates = ['2025-04-01', '2025-05-01']; // Add more dates as needed
        $trends = [];
        
        foreach ($dates as $date) {
            $analysis = $this->bcgAnalysisService->generateAnalysis($date);
            $trends[$date] = $analysis['overview'];
        }
        
        return response()->json($trends);
    }

    /**
     * Bulk update product strategies
     */
    public function updateProductStrategies(Request $request)
    {
        $updates = $request->input('updates', []);
        
        foreach ($updates as $update) {
            BCGProduct::where('kode_produk', $update['kode_produk'])
                    ->where('date', $update['date'])
                    ->update([
                        'strategy_notes' => $update['strategy'] ?? null,
                        'action_items' => json_encode($update['actions'] ?? []),
                        'updated_at' => now()
                    ]);
        }
        
        return response()->json(['message' => 'Strategies updated successfully']);
    }

    /**
     * Get product details with history
     */
    public function getProductDetails($kode_produk)
    {
        $product = BCGProduct::where('kode_produk', $kode_produk)
                            ->where('date', '2025-05-01')
                            ->first();
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        $details = [
            'basic_info' => $product,
            'calculated_metrics' => [
                'conversion_rate' => $product->conversion_rate,
                'atc_rate' => $product->atc_rate,
                'purchase_rate' => $product->purchase_rate,
                'roas' => $product->roas,
                'revenue_per_visitor' => $product->revenue_per_visitor,
                'stock_turnover' => $product->stock_turnover,
                'performance_score' => $product->performance_score
            ],
            'bcg_classification' => [
                'quadrant' => $product->bcg_quadrant,
                'benchmark_conversion' => $product->benchmark_conversion,
                'quadrant_color' => $product->quadrant_color
            ],
            'recommendations' => $this->getProductRecommendations($product)
        ];
        
        return response()->json($details);
    }

    /**
     * Generate specific recommendations for a product
     */
    private function getProductRecommendations($product)
    {
        $recommendations = [];
        
        switch ($product->bcg_quadrant) {
            case 'Stars':
                $recommendations = [
                    'primary' => 'Increase investment and scale',
                    'actions' => [
                        'Boost advertising budget by 20-30%',
                        'Ensure stock availability',
                        'Monitor competitor activities',
                        'Consider premium positioning'
                    ]
                ];
                break;
                
            case 'Cash Cows':
                $recommendations = [
                    'primary' => 'Optimize for profitability',
                    'actions' => [
                        'Reduce ad spend gradually',
                        'Increase profit margins',
                        'Maintain quality standards',
                        'Use profits to fund other products'
                    ]
                ];
                break;
                
            case 'Question Marks':
                $recommendations = [
                    'primary' => 'Improve conversion rate',
                    'actions' => [
                        'Optimize product pages',
                        'Review pricing strategy',
                        'Improve product images/descriptions',
                        'Test different targeting'
                    ]
                ];
                break;
                
            case 'Dogs':
                $recommendations = [
                    'primary' => 'Consider discontinuation',
                    'actions' => [
                        'Stop advertising spend',
                        'Liquidate inventory',
                        'Analyze failure reasons',
                        'Consider product repositioning'
                    ]
                ];
                break;
        }
        
        // Add specific recommendations based on metrics
        if ($product->roas < 1 && $product->biaya_ads > 500000) {
            $recommendations['urgent'][] = 'URGENT: Poor ROAS with high ad spend - review immediately';
        }
        
        if ($product->stock_turnover < 0.1 && $product->stock > 1000) {
            $recommendations['urgent'][] = 'URGENT: Very slow stock movement - consider price reduction';
        }
        
        return $recommendations;
    }

    /**
     * Advanced filtering and search
     */
    public function advancedFilter(Request $request)
    {
        $query = BCGProduct::withCompleteData()->where('date', '2025-05-01');
        
        // Apply filters
        if ($request->has('quadrant') && $request->quadrant !== 'all') {
            $products = $query->get()->filter(function($product) use ($request) {
                return $product->bcg_quadrant === $request->quadrant;
            });
        } else {
            $products = $query->get();
        }
        
        if ($request->has('min_revenue')) {
            $products = $products->where('sales', '>=', $request->min_revenue);
        }
        
        if ($request->has('min_conversion')) {
            $products = $products->where('conversion_rate', '>=', $request->min_conversion);
        }
        
        if ($request->has('max_roas')) {
            $products = $products->where('roas', '<=', $request->max_roas);
        }
        
        // Sort options
        $sortBy = $request->get('sort_by', 'sales');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if ($sortDirection === 'desc') {
            $products = $products->sortByDesc($sortBy);
        } else {
            $products = $products->sortBy($sortBy);
        }
        
        return response()->json([
            'products' => $products->values(),
            'total' => $products->count(),
            'filters_applied' => $request->all()
        ]);
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
    public function importBcgAds()
    {
        $this->googleSheetService->setSpreadsheetId('1MnY6beeJjZIJ_lMWytdPb6shLlX7gkselbynkRfELbE');
        $range = 'IKLAN SHOPEE!A2:X'; // Assuming data starts from row 2
        $sheetData = $this->googleSheetService->getSheetData($range);
        
        $date = '2025-05-01'; // Same date as used in product import
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $notFoundRows = 0;
        
        // First, collect all data and group by kode_produk to calculate sums
        $groupedData = [];
        
        foreach ($sheetData as $row) {
            // Skip if kode_produk is missing (Column E)
            if (empty($row[4])) {
                $skippedRows++;
                continue;
            }
            
            $kode_produk = trim($row[4]); // Column E
            
            // Handle formatted numbers for biaya_ads (Column X) and omset_penjualan (Column V)
            $biaya_ads = null;
            if (isset($row[23]) && !empty($row[23])) {
                $cleanedBiayaAds = str_replace('.', '', trim($row[23]));
                $biaya_ads = is_numeric($cleanedBiayaAds) ? (int)$cleanedBiayaAds : null;
            }
            
            $omset_penjualan = null;
            if (isset($row[21]) && !empty($row[21])) {
                $cleanedOmset = str_replace('.', '', trim($row[21]));
                $omset_penjualan = is_numeric($cleanedOmset) ? (int)$cleanedOmset : null;
            }
            
            // Group data by kode_produk
            if (!isset($groupedData[$kode_produk])) {
                $groupedData[$kode_produk] = [
                    'biaya_ads_values' => [],
                    'omset_penjualan_values' => []
                ];
            }
            
            // Add values to arrays (only if not null)
            if ($biaya_ads !== null) {
                $groupedData[$kode_produk]['biaya_ads_values'][] = $biaya_ads;
            }
            if ($omset_penjualan !== null) {
                $groupedData[$kode_produk]['omset_penjualan_values'][] = $omset_penjualan;
            }
        }
        
        // Now process the grouped data and calculate sums
        foreach ($groupedData as $kode_produk => $data) {
            // Calculate sums
            $sum_biaya_ads = !empty($data['biaya_ads_values']) ? 
                array_sum($data['biaya_ads_values']) : null;
                
            $sum_omset_penjualan = !empty($data['omset_penjualan_values']) ? 
                array_sum($data['omset_penjualan_values']) : null;
            
            // Find existing product by kode_produk AND date
            $existingProduct = BcgProduct::where('kode_produk', $kode_produk)
                                        ->where('date', $date)
                                        ->first();
            
            if (!$existingProduct) {
                $notFoundRows++;
                continue; // Skip if product doesn't exist for this date
            }
            
            // Update the existing product with summed biaya_ads and omset_penjualan
            $existingProduct->update([
                'biaya_ads' => $sum_biaya_ads,
                'omset_penjualan' => $sum_omset_penjualan,
                'updated_at' => now(),
            ]);
            
            $processedRows++;
        }
        
        return response()->json([
            'message' => 'BCG Ads data imported successfully with sums calculated',
            'total_rows' => $totalRows,
            'unique_products' => count($groupedData),
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'not_found_rows' => $notFoundRows
        ]);
    }
}
