<?php

namespace App\Domain\Sales\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdsMeta3 extends Model
{
    use HasFactory;

    protected $table = 'ads_monitoring';

    protected $fillable = [
        'date',
        'channel',
        'gmv_target',
        'spent_target',
        'roas_target',
        'cpa_target',
        'aov_to_cpa_target',
        'gmv_actual',
        'spent_actual',
        'roas_actual',
        'cpa_actual',
        'aov_to_cpa_actual'
    ];

    protected $casts = [
        'date' => 'date',
        'gmv_target' => 'decimal:2',
        'spent_target' => 'decimal:2',
        'roas_target' => 'decimal:4',
        'cpa_target' => 'decimal:2',
        'aov_to_cpa_target' => 'decimal:4',
        'gmv_actual' => 'decimal:2',
        'spent_actual' => 'decimal:2',
        'roas_actual' => 'decimal:4',
        'cpa_actual' => 'decimal:2',
        'aov_to_cpa_actual' => 'decimal:4'
    ];

    /**
     * Calculate GMV variance percentage
     */
    public function getGmvVarianceAttribute()
    {
        if ($this->gmv_target && $this->gmv_actual) {
            return (($this->gmv_actual - $this->gmv_target) / $this->gmv_target) * 100;
        }
        return null;
    }

    /**
     * Calculate Spent variance percentage
     */
    public function getSpentVarianceAttribute()
    {
        if ($this->spent_target && $this->spent_actual) {
            return (($this->spent_actual - $this->spent_target) / $this->spent_target) * 100;
        }
        return null;
    }

    /**
     * Calculate ROAS variance percentage
     */
    public function getRoasVarianceAttribute()
    {
        if ($this->roas_target && $this->roas_actual) {
            return (($this->roas_actual - $this->roas_target) / $this->roas_target) * 100;
        }
        return null;
    }

    /**
     * Calculate CPA variance percentage
     */
    public function getCpaVarianceAttribute()
    {
        if ($this->cpa_target && $this->cpa_actual) {
            return (($this->cpa_actual - $this->cpa_target) / $this->cpa_target) * 100;
        }
        return null;
    }

    /**
     * Get performance status
     */
    public function getPerformanceStatusAttribute()
    {
        $scores = [];
        
        // GMV Performance
        if ($this->gmv_target && $this->gmv_actual) {
            $scores[] = ($this->gmv_actual >= $this->gmv_target) ? 1 : 0;
        }
        
        // ROAS Performance
        if ($this->roas_target && $this->roas_actual) {
            $scores[] = ($this->roas_actual >= $this->roas_target) ? 1 : 0;
        }
        
        // CPA Performance (lower is better)
        if ($this->cpa_target && $this->cpa_actual) {
            $scores[] = ($this->cpa_actual <= $this->cpa_target) ? 1 : 0;
        }
        
        if (empty($scores)) {
            return 'no_data';
        }
        
        $avgScore = array_sum($scores) / count($scores);
        
        if ($avgScore >= 0.8) {
            return 'excellent';
        } elseif ($avgScore >= 0.6) {
            return 'good';
        } elseif ($avgScore >= 0.4) {
            return 'average';
        } else {
            return 'poor';
        }
    }

    /**
     * Scope for date range filtering
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for channel filtering
     */
    public function scopeChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Get unique channels
     */
    public static function getChannels()
    {
        return self::distinct('channel')->pluck('channel')->sort()->values();
    }
}
