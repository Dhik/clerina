<?php

namespace App\Domain\BCGMetrics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcgProduct extends Model
{
    use HasFactory;

    protected $table = 'bcg_product';

    protected $fillable = [
        'date',
        'tenant_id',
        'kode_produk',
        'nama_produk',
        'sku',
        'visitor',
        'jumlah_atc',
        'jumlah_pembeli',
        'qty_sold',
        'sales',
        'stock',
        'harga',
        'biaya_ads',
        'omset_penjualan',
    ];

    protected $casts = [
        'date' => 'date',
        'tenant_id' => 'integer',
        'visitor' => 'integer',
        'jumlah_atc' => 'integer',
        'jumlah_pembeli' => 'integer',
        'qty_sold' => 'integer',
        'sales' => 'integer',
        'stock' => 'integer',
        'harga' => 'integer',
        'biaya_ads' => 'integer',
        'omset_penjualan' => 'integer',
    ];

    public function getConversionRateAttribute()
    {
        return $this->visitor > 0 ? round(($this->jumlah_pembeli / $this->visitor) * 100, 2) : 0;
    }

    public function getAtcRateAttribute() 
    {
        return $this->visitor > 0 ? round(($this->jumlah_atc / $this->visitor) * 100, 2) : 0;
    }

    public function getPurchaseRateAttribute()
    {
        return $this->jumlah_atc > 0 ? round(($this->jumlah_pembeli / $this->jumlah_atc) * 100, 2) : 0;
    }

    public function getRoasAttribute()
    {
        return $this->biaya_ads > 0 ? round($this->omset_penjualan / $this->biaya_ads, 2) : 0;
    }

    public function getRevenuePerVisitorAttribute()
    {
        return $this->visitor > 0 ? round($this->sales / $this->visitor, 0) : 0;
    }

    public function getStockTurnoverAttribute()
    {
        return $this->stock > 0 ? round($this->qty_sold / $this->stock, 2) : 0;
    }

    public function getBenchmarkConversionAttribute()
    {
        if ($this->harga < 75000) return 2.0;
        if ($this->harga < 100000) return 1.5;  
        if ($this->harga < 125000) return 1.0;
        if ($this->harga < 150000) return 0.8;
        return 0.6;
    }

    public function getBcgQuadrantAttribute()
    {
        $medianTraffic = static::getMedianTraffic($this->date);
        
        $isHighTraffic = $this->visitor >= $medianTraffic;
        $isHighConversion = $this->conversion_rate >= $this->benchmark_conversion;
        
        if ($isHighTraffic && $isHighConversion) return 'Stars';
        if ($isHighTraffic && !$isHighConversion) return 'Question Marks';
        if (!$isHighTraffic && $isHighConversion) return 'Cash Cows';
        return 'Dogs';
    }

    public function getQuadrantColorAttribute()
    {
        $colors = [
            'Stars' => '#28a745',
            'Cash Cows' => '#ffc107', 
            'Question Marks' => '#17a2b8',
            'Dogs' => '#dc3545'
        ];
        return $colors[$this->bcg_quadrant] ?? '#6c757d';
    }

    public function getPerformanceScoreAttribute()
    {
        $conversionScore = $this->conversion_rate >= $this->benchmark_conversion ? 25 : 0;
        $roasScore = $this->roas >= 3 ? 25 : ($this->roas >= 1 ? 15 : 0);
        $trafficScore = $this->visitor >= static::getMedianTraffic($this->date) ? 25 : 10;
        $stockScore = $this->stock_turnover >= 1 ? 25 : ($this->stock_turnover >= 0.5 ? 15 : 0);
        
        return $conversionScore + $roasScore + $trafficScore + $stockScore;
    }

    // Scopes
    public function scopeWithCompleteData(Builder $query)
    {
        return $query->whereNotNull('visitor')
                    ->whereNotNull('jumlah_pembeli') 
                    ->whereNotNull('harga')
                    ->where('visitor', '>', 0);
    }

    public function scopeByQuadrant(Builder $query, $quadrant)
    {
        return $query->withCompleteData()->get()->filter(function($product) use ($quadrant) {
            return $product->bcg_quadrant === $quadrant;
        });
    }

    public function scopeByDate(Builder $query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeTopPerformers(Builder $query, $limit = 10)
    {
        return $query->withCompleteData()->get()
                    ->sortByDesc('performance_score')
                    ->take($limit);
    }

    // Static methods
    public static function getMedianTraffic($date = '2025-05-01')
    {
        return static::withCompleteData()
                    ->where('date', $date)
                    ->get()
                    ->median('visitor') ?? 0;
    }

    public static function getQuadrantSummary($date = '2025-05-01')
    {
        $products = static::withCompleteData()->where('date', $date)->get();
        
        return $products->groupBy('bcg_quadrant')->map(function ($group, $quadrant) {
            return [
                'quadrant' => $quadrant,
                'count' => $group->count(),
                'total_revenue' => $group->sum('sales'),
                'total_ads_cost' => $group->sum('biaya_ads'),
                'avg_conversion' => round($group->avg('conversion_rate'), 2),
                'avg_traffic' => round($group->avg('visitor'), 0),
                'total_stock' => $group->sum('stock'),
                'avg_roas' => round($group->avg('roas'), 2),
                'avg_performance_score' => round($group->avg('performance_score'), 1)
            ];
        });
    }
}