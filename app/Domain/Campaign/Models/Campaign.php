<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Campaign extends Model implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'description',
        'tenant_id',
        'created_by',
        'view',
        'like',
        'comment',
        'total_influencer',
        'total_content',
        'total_expense',
        'achievement',
        'cpm',
        'id_budget',
    ];

    protected $appends = [
        'period',
        'expense_formatted',
        'cpm_formatted',
        'views_formatted'
    ];

    public function statistics()
    {
        return $this->hasMany(Statistic::class);
    }


    /**
     * Casting start date
     */
    public function getStartDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d M Y');
    }

    /**
     * Casting end date
     */
    public function getEndDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d M Y');
    }

    /**
     * Relation to campaign content
     */
    public function campaignContents(): HasMany
    {
        return $this->hasMany(CampaignContent::class);
    }

    /**
     * Return period format
     */
    public function getPeriodAttribute(): string
    {
        $startDate = Carbon::parse($this->start_date)->format('d/m/Y');
        $endDate = Carbon::parse($this->end_date)->format('d/m/Y');

        return $startDate . ' - ' . $endDate;
    }

    /**
     * Return Expense format
     */
    public function getCpmFormattedAttribute(): string
    {
        return number_format($this->cpm, '2', ',', '.');
    }

    /**
     * Return CPM format
     */
    public function getExpenseFormattedAttribute(): string
    {
        return number_format($this->total_expense, '0', ',', '.');
    }

    /**
     * Return Views format
     */
    public function getViewsFormattedAttribute(): string
    {
        return $this->numberFormatShort($this->view);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    protected function numberFormatShort($n, $precision = 1): string
    {
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        } elseif ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n * 0.001, $precision);
            $suffix = 'K';
        } elseif ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n * 0.000001, $precision);
            $suffix = 'M';
        } elseif ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n * 0.000000001, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n * 0.000000000001, $precision);
            $suffix = 'T';
        }

        // Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ($precision > 0) {
            $dotZero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotZero, '', $n_format);
        }

        return $n_format . $suffix;
    }
}
