<?php

namespace App\Domain\BCGMetrics\Services;

use App\Domain\BCGMetrics\Models\BCGProduct;
use Illuminate\Support\Collection;

class BCGAnalysisService
{
    /**
     * Generate comprehensive BCG analysis
     */
    public function generateAnalysis($date = '2025-05-01'): array
    {
        // Get products and group by SKU
        $products = BCGProduct::whereNotNull('visitor')
            ->whereNotNull('jumlah_pembeli')
            ->whereNotNull('harga')
            ->whereNotNull('sku')
            ->where('visitor', '>', 0)
            ->where('date', $date)
            ->get();

        // Group by SKU and aggregate
        $groupedProducts = $products->groupBy('sku')->map(function ($group) {
            $first = $group->first();
            return (object)[
                'kode_produk' => $first->kode_produk,
                'nama_produk' => $first->nama_produk,
                'sku' => $first->sku,
                'visitor' => $group->sum('visitor'),
                'jumlah_pembeli' => $group->sum('jumlah_pembeli'),
                'qty_sold' => $group->sum('qty_sold'),
                'sales' => $group->sum('sales'),
                'stock' => $group->sum('stock'),
                'harga' => $group->avg('harga'),
                'biaya_ads' => $group->sum('biaya_ads'),
                'omset_penjualan' => $group->sum('omset_penjualan'),
            ];
        })->values();

        return [
            'overview' => $this->getOverview($groupedProducts),
            'quadrant_analysis' => $this->getQuadrantAnalysis($groupedProducts),
            'recommendations' => $this->getRecommendations($groupedProducts),
            'top_performers' => $this->getTopPerformers($groupedProducts),
            'opportunities' => $this->getOpportunities($groupedProducts),
            'risk_products' => $this->getRiskProducts($groupedProducts)
        ];
    }

    /**
     * Get overall portfolio overview
     */
    private function getOverview(Collection $products): array
    {
        $totalRevenue = $products->sum('sales');
        $totalAdsCost = $products->sum('biaya_ads');
        
        return [
            'total_products' => $products->count(),
            'total_revenue' => $totalRevenue,
            'total_ads_cost' => $totalAdsCost,
            'portfolio_roas' => $totalAdsCost > 0 ? round($totalRevenue / $totalAdsCost, 2) : 0,
            'avg_conversion_rate' => round($products->avg(function($p) { 
                return $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
            }), 2),
            'total_traffic' => $products->sum('visitor'),
            'total_stock_value' => $products->sum(function($p) { return ($p->stock ?? 0) * ($p->harga ?? 0); }),
            'stock_turnover_ratio' => $products->sum('stock') > 0 ? round($products->sum('qty_sold') / $products->sum('stock'), 2) : 0
        ];
    }

    /**
     * Detailed quadrant analysis
     */
    private function getQuadrantAnalysis(Collection $products): array
    {
        $medianTraffic = $products->median('visitor');
        
        // Calculate quadrants for each product
        $productsWithQuadrants = $products->map(function ($product) use ($medianTraffic) {
            $conversionRate = $product->visitor > 0 ? ($product->jumlah_pembeli / $product->visitor) * 100 : 0;
            $benchmarkConversion = $this->getBenchmarkConversion($product->harga);
            
            $isHighTraffic = $product->visitor >= $medianTraffic;
            $isHighConversion = $conversionRate >= $benchmarkConversion;
            
            if ($isHighTraffic && $isHighConversion) {
                $quadrant = 'Stars';
            } elseif ($isHighTraffic && !$isHighConversion) {
                $quadrant = 'Question Marks';
            } elseif (!$isHighTraffic && $isHighConversion) {
                $quadrant = 'Cash Cows';
            } else {
                $quadrant = 'Dogs';
            }
            
            $product->bcg_quadrant = $quadrant;
            $product->conversion_rate = $conversionRate;
            $product->roas = $product->biaya_ads > 0 ? ($product->omset_penjualan ?? 0) / $product->biaya_ads : 0;
            
            return $product;
        });
        
        $quadrants = $productsWithQuadrants->groupBy('bcg_quadrant');
        
        $analysis = [];
        foreach (['Stars', 'Cash Cows', 'Question Marks', 'Dogs'] as $quadrant) {
            $quadrantProducts = $quadrants->get($quadrant, collect());
            
            $analysis[$quadrant] = [
                'count' => $quadrantProducts->count(),
                'revenue_contribution' => $quadrantProducts->sum('sales'),
                'revenue_percentage' => $products->sum('sales') > 0 ? 
                    round(($quadrantProducts->sum('sales') / $products->sum('sales')) * 100, 1) : 0,
                'avg_conversion' => round($quadrantProducts->avg('conversion_rate'), 2),
                'avg_roas' => round($quadrantProducts->avg('roas'), 2),
                'total_stock_value' => $quadrantProducts->sum(function($p) { 
                    return ($p->stock ?? 0) * ($p->harga ?? 0); 
                }),
                'top_products' => $quadrantProducts->sortByDesc('sales')->take(5)->values(),
                'avg_performance_score' => round($quadrantProducts->avg(function($p) use ($medianTraffic) {
                    return $this->calculatePerformanceScore($p, $medianTraffic);
                }), 1)
            ];
        }
        
        return $analysis;
    }

    /**
     * Generate strategic recommendations
     */
    private function getRecommendations(Collection $products): array
    {
        $medianTraffic = $products->median('visitor');
        
        $productsWithQuadrants = $products->map(function ($product) use ($medianTraffic) {
            $conversionRate = $product->visitor > 0 ? ($product->jumlah_pembeli / $product->visitor) * 100 : 0;
            $benchmarkConversion = $this->getBenchmarkConversion($product->harga);
            
            $isHighTraffic = $product->visitor >= $medianTraffic;
            $isHighConversion = $conversionRate >= $benchmarkConversion;
            
            if ($isHighTraffic && $isHighConversion) {
                $quadrant = 'Stars';
            } elseif ($isHighTraffic && !$isHighConversion) {
                $quadrant = 'Question Marks';
            } elseif (!$isHighTraffic && $isHighConversion) {
                $quadrant = 'Cash Cows';
            } else {
                $quadrant = 'Dogs';
            }
            
            $product->bcg_quadrant = $quadrant;
            $product->conversion_rate = $conversionRate;
            $product->roas = $product->biaya_ads > 0 ? ($product->omset_penjualan ?? 0) / $product->biaya_ads : 0;
            
            return $product;
        });
        
        $quadrants = $productsWithQuadrants->groupBy('bcg_quadrant');
        
        $recommendations = [];
        
        // Stars recommendations
        $stars = $quadrants->get('Stars', collect());
        if ($stars->count() > 0) {
            $recommendations['Stars'] = [
                'strategy' => 'Invest & Grow',
                'actions' => [
                    'Increase advertising budget for top performing Stars',
                    'Ensure adequate stock levels to meet demand',
                    'Monitor for market saturation signals',
                    'Consider product variations or bundles'
                ],
                'priority_products' => $stars->sortByDesc('sales')->take(3)->pluck('sku')->toArray()
            ];
        }

        // Cash Cows recommendations  
        $cashCows = $quadrants->get('Cash Cows', collect());
        if ($cashCows->count() > 0) {
            $recommendations['Cash Cows'] = [
                'strategy' => 'Harvest & Optimize',
                'actions' => [
                    'Focus on profit margin optimization',
                    'Reduce advertising spend while maintaining position',
                    'Consider premium pricing strategy',
                    'Use profits to fund Stars and Question Marks'
                ],
                'priority_products' => $cashCows->sortByDesc('roas')->take(3)->pluck('sku')->toArray()
            ];
        }

        // Question Marks recommendations
        $questionMarks = $quadrants->get('Question Marks', collect());
        if ($questionMarks->count() > 0) {
            $recommendations['Question Marks'] = [
                'strategy' => 'Analyze & Decide',
                'actions' => [
                    'Conduct conversion rate optimization',
                    'Review product positioning and pricing',
                    'A/B test different marketing approaches',
                    'Consider targeting different customer segments'
                ],
                'priority_products' => $questionMarks->sortByDesc('visitor')->take(3)->pluck('sku')->toArray()
            ];
        }

        // Dogs recommendations
        $dogs = $quadrants->get('Dogs', collect());
        if ($dogs->count() > 0) {
            $recommendations['Dogs'] = [
                'strategy' => 'Divest or Reposition',
                'actions' => [
                    'Discontinue poor performers',
                    'Liquidate excess inventory',
                    'Stop advertising spend',
                    'Consider repositioning viable products'
                ],
                'priority_products' => $dogs->sortBy('sales')->take(3)->pluck('sku')->toArray()
            ];
        }

        return $recommendations;
    }

    /**
     * Identify top performing products across all quadrants
     */
    private function getTopPerformers(Collection $products): array
    {
        $medianTraffic = $products->median('visitor'); // Calculate median traffic once
        
        return [
            'by_revenue' => $products->sortByDesc('sales')->take(10)->values(),
            'by_conversion' => $products->sortByDesc(function($p) {
                return $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
            })->take(10)->values(),
            'by_roas' => $products->filter(function($p) {
                return $p->biaya_ads > 0;
            })->sortByDesc(function($p) {
                return ($p->omset_penjualan ?? 0) / $p->biaya_ads;
            })->take(10)->values(),
            'by_performance_score' => $products->sortByDesc(function($p) use ($medianTraffic) { // Add use clause
                return $this->calculatePerformanceScore($p, $medianTraffic);
            })->take(10)->values()
        ];
    }

    /**
     * Identify growth opportunities
     */
    private function getOpportunities(Collection $products): array
    {
        $medianTraffic = $products->median('visitor');
        
        return [
            'high_traffic_low_conversion' => $products->filter(function($p) use ($medianTraffic) {
                $conversionRate = $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
                $benchmarkConversion = $this->getBenchmarkConversion($p->harga);
                return $p->visitor > $medianTraffic && $conversionRate < $benchmarkConversion;
            })->sortByDesc('visitor')->take(10)->values(),
            
            'underinvested_stars' => $products->filter(function($p) use ($medianTraffic) {
                $conversionRate = $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
                $benchmarkConversion = $this->getBenchmarkConversion($p->harga);
                $roas = $p->biaya_ads > 0 ? ($p->omset_penjualan ?? 0) / $p->biaya_ads : 0;
                
                $isHighTraffic = $p->visitor >= $medianTraffic;
                $isHighConversion = $conversionRate >= $benchmarkConversion;
                
                return $isHighTraffic && $isHighConversion && $roas > 5;
            })->sortByDesc(function($p) {
                return $p->biaya_ads > 0 ? ($p->omset_penjualan ?? 0) / $p->biaya_ads : 0;
            })->take(5)->values(),
            
            'cash_cow_candidates' => $products->filter(function($p) use ($medianTraffic) {
                $conversionRate = $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
                $benchmarkConversion = $this->getBenchmarkConversion($p->harga);
                
                $isHighTraffic = $p->visitor >= $medianTraffic;
                $isHighConversion = $conversionRate >= $benchmarkConversion;
                
                return $isHighTraffic && !$isHighConversion && $conversionRate >= $benchmarkConversion * 0.8;
            })->sortByDesc(function($p) {
                return $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
            })->take(5)->values()
        ];
    }

    /**
     * Identify products at risk
     */
    private function getRiskProducts(Collection $products): array
    {
        $medianTraffic = $products->median('visitor');
        
        return [
            'declining_stars' => $products->filter(function($p) use ($medianTraffic) {
                $conversionRate = $p->visitor > 0 ? ($p->jumlah_pembeli / $p->visitor) * 100 : 0;
                $benchmarkConversion = $this->getBenchmarkConversion($p->harga);
                $roas = $p->biaya_ads > 0 ? ($p->omset_penjualan ?? 0) / $p->biaya_ads : 0;
                
                $isHighTraffic = $p->visitor >= $medianTraffic;
                $isHighConversion = $conversionRate >= $benchmarkConversion;
                
                return $isHighTraffic && $isHighConversion && $roas < 2;
            })->sortBy(function($p) {
                return $p->biaya_ads > 0 ? ($p->omset_penjualan ?? 0) / $p->biaya_ads : 0;
            })->take(5)->values(),
            
            'overstocked' => $products->filter(function($p) {
                $stockTurnover = ($p->stock ?? 0) > 0 ? ($p->qty_sold ?? 0) / $p->stock : 0;
                return $stockTurnover < 0.3 && ($p->stock ?? 0) > 100;
            })->sortByDesc('stock')->take(10)->values(),
            
            'high_ads_low_return' => $products->filter(function($p) {
                $roas = $p->biaya_ads > 0 ? ($p->omset_penjualan ?? 0) / $p->biaya_ads : 0;
                return ($p->biaya_ads ?? 0) > 1000000 && $roas < 1;
            })->sortBy(function($p) {
                return $p->biaya_ads > 0 ? ($p->omset_penjualan ?? 0) / $p->biaya_ads : 0;
            })->take(10)->values()
        ];
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
     * Calculate performance score for a product
     */
    private function calculatePerformanceScore($product, $medianTraffic)
    {
        $conversionRate = $product->visitor > 0 ? ($product->jumlah_pembeli / $product->visitor) * 100 : 0;
        $benchmarkConversion = $this->getBenchmarkConversion($product->harga);
        $roas = $product->biaya_ads > 0 ? ($product->omset_penjualan ?? 0) / $product->biaya_ads : 0;
        $stockTurnover = ($product->stock ?? 0) > 0 ? ($product->qty_sold ?? 0) / $product->stock : 0;
        
        $conversionScore = $conversionRate >= $benchmarkConversion ? 25 : 0;
        $roasScore = $roas >= 3 ? 25 : ($roas >= 1 ? 15 : 0);
        $trafficScore = $product->visitor >= $medianTraffic ? 25 : 10;
        $stockScore = $stockTurnover >= 1 ? 25 : ($stockTurnover >= 0.5 ? 15 : 0);
        
        return $conversionScore + $roasScore + $trafficScore + $stockScore;
    }

    /**
     * Export analysis to different formats
     */
    public function exportAnalysis($format = 'array', $date = '2025-05-01')
    {
        $analysis = $this->generateAnalysis($date);
        
        switch ($format) {
            case 'json':
                return json_encode($analysis, JSON_PRETTY_PRINT);
            case 'csv':
                return $this->exportToCsv($analysis);
            default:
                return $analysis;
        }
    }

    /**
     * Convert analysis to CSV format
     */
    private function exportToCsv($analysis): string
    {
        $csv = "Product Analysis Report\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        // Overview
        $csv .= "OVERVIEW\n";
        foreach ($analysis['overview'] as $key => $value) {
            $csv .= "$key,$value\n";
        }
        
        $csv .= "\nQUADRANT ANALYSIS\n";
        $csv .= "Quadrant,Count,Revenue,Revenue %,Avg Conversion,Avg ROAS,Performance Score\n";
        
        foreach ($analysis['quadrant_analysis'] as $quadrant => $data) {
            $csv .= "$quadrant,{$data['count']},{$data['revenue_contribution']},{$data['revenue_percentage']}%,{$data['avg_conversion']}%,{$data['avg_roas']},{$data['avg_performance_score']}\n";
        }
        
        return $csv;
    }
}