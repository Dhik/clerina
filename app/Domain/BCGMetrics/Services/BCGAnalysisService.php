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
        $products = BCGProduct::withCompleteData()->where('date', $date)->get();
        
        return [
            'overview' => $this->getOverview($products),
            'quadrant_analysis' => $this->getQuadrantAnalysis($products),
            'recommendations' => $this->getRecommendations($products),
            'top_performers' => $this->getTopPerformers($products),
            'opportunities' => $this->getOpportunities($products),
            'risk_products' => $this->getRiskProducts($products)
        ];
    }

    /**
     * Get overall portfolio overview
     */
    private function getOverview(Collection $products): array
    {
        $totalRevenue = $products->sum('sales');
        $totalAdsCost = $products->sum('biaya_ads');
        $totalStock = $products->sum('stock');
        
        return [
            'total_products' => $products->count(),
            'total_revenue' => $totalRevenue,
            'total_ads_cost' => $totalAdsCost,
            'portfolio_roas' => $totalAdsCost > 0 ? round($totalRevenue / $totalAdsCost, 2) : 0,
            'avg_conversion_rate' => round($products->avg('conversion_rate'), 2),
            'total_traffic' => $products->sum('visitor'),
            'total_stock_value' => $products->sum(function($p) { return $p->stock * $p->harga; }),
            'stock_turnover_ratio' => $totalStock > 0 ? round($products->sum('qty_sold') / $totalStock, 2) : 0
        ];
    }

    /**
     * Detailed quadrant analysis
     */
    private function getQuadrantAnalysis(Collection $products): array
    {
        $quadrants = $products->groupBy('bcg_quadrant');
        
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
                    return $p->stock * $p->harga; 
                }),
                'top_products' => $quadrantProducts->sortByDesc('sales')->take(5)->values(),
                'avg_performance_score' => round($quadrantProducts->avg('performance_score'), 1)
            ];
        }
        
        return $analysis;
    }

    /**
     * Generate strategic recommendations
     */
    private function getRecommendations(Collection $products): array
    {
        $quadrants = $products->groupBy('bcg_quadrant');
        
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
                'priority_products' => $stars->sortByDesc('performance_score')->take(3)->pluck('nama_produk')->toArray()
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
                'priority_products' => $cashCows->sortByDesc('roas')->take(3)->pluck('nama_produk')->toArray()
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
                'priority_products' => $questionMarks->sortByDesc('visitor')->take(3)->pluck('nama_produk')->toArray()
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
                'priority_products' => $dogs->sortBy('performance_score')->take(3)->pluck('nama_produk')->toArray()
            ];
        }

        return $recommendations;
    }

    /**
     * Identify top performing products across all quadrants
     */
    private function getTopPerformers(Collection $products): array
    {
        return [
            'by_revenue' => $products->sortByDesc('sales')->take(10)->values(),
            'by_conversion' => $products->sortByDesc('conversion_rate')->take(10)->values(),
            'by_roas' => $products->where('roas', '>', 0)->sortByDesc('roas')->take(10)->values(),
            'by_performance_score' => $products->sortByDesc('performance_score')->take(10)->values()
        ];
    }

    /**
     * Identify growth opportunities
     */
    private function getOpportunities(Collection $products): array
    {
        return [
            'high_traffic_low_conversion' => $products->filter(function($p) {
                return $p->visitor > BCGProduct::getMedianTraffic() && 
                       $p->conversion_rate < $p->benchmark_conversion;
            })->sortByDesc('visitor')->take(10)->values(),
            
            'underinvested_stars' => $products->filter(function($p) {
                return $p->bcg_quadrant === 'Stars' && $p->roas > 5;
            })->sortByDesc('roas')->take(5)->values(),
            
            'cash_cow_candidates' => $products->filter(function($p) {
                return $p->bcg_quadrant === 'Question Marks' && 
                       $p->conversion_rate >= $p->benchmark_conversion * 0.8;
            })->sortByDesc('conversion_rate')->take(5)->values()
        ];
    }

    /**
     * Identify products at risk
     */
    private function getRiskProducts(Collection $products): array
    {
        return [
            'declining_stars' => $products->filter(function($p) {
                return $p->bcg_quadrant === 'Stars' && $p->roas < 2;
            })->sortBy('roas')->take(5)->values(),
            
            'overstocked' => $products->filter(function($p) {
                return $p->stock_turnover < 0.3 && $p->stock > 100;
            })->sortByDesc('stock')->take(10)->values(),
            
            'high_ads_low_return' => $products->filter(function($p) {
                return $p->biaya_ads > 1000000 && $p->roas < 1;
            })->sortBy('roas')->take(10)->values()
        ];
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