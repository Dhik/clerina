<?php

namespace App\Domain\Contest\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestContent extends Model
{
    protected $fillable = [
        'contest_id',
        'link',
        'view',
        'like',
        'comment',
        'share',
        'interaction',
        'upload_date',
        'duration',
        'follower',
        'username',
        'name',
        'rate',
        'rate_total'
    ];

    protected $appends = [
        'human_duration',
        'view_formatted',
        'like_formatted',
        'comment_formatted',
        'share_formatted',
        'interaction_formatted',
        'social_media_link',
        'rate_formatted',
        'rate_total_formatted'
    ];

    public function getViewFormattedAttribute(): string
    {
        return $this->numberFormatShort($this->view);
    }

    public function getLikeFormattedAttribute(): string
    {
        return $this->numberFormatShort($this->like);
    }

    public function getCommentFormattedAttribute(): string
    {
        return $this->numberFormatShort($this->comment);
    }

    public function getShareFormattedAttribute(): string
    {
        return $this->numberFormatShort($this->share);
    }

    public function getInteractionFormattedAttribute(): string
    {
        return $this->interaction . '%';
    }

    public function getRateFormattedAttribute(): string
    {
        return 'Rp. ' . number_format($this->rate, '0', ',', '.');
    }

    public function getRateTotalFormattedAttribute(): string
    {
        return 'Rp. ' . number_format($this->rate_total, '0', ',', '.');
    }

    /**
     * Return social media link
     */
    public function getSocialMediaLinkAttribute(): string
    {
        return 'https://www.tiktok.com/@' . $this->username;
    }

    /**
     * Casting start date
     */
    public function getHumanDurationAttribute(): string
    {
        if ($this->duration === 0) {
            return '0';
        }

        $seconds = $this->duration / 1000;
        $interval = CarbonInterval::seconds($seconds)->cascade();

        $parts = [];

        if ($interval->h > 0) {
            $parts[] = $interval->h . 'h';
        }
        if ($interval->i > 0) {
            $parts[] = $interval->i . 'm';
        }
        if ($interval->s > 0) {
            $parts[] = $interval->s . 's';
        }

        // Join the parts with a space
        return implode(' ', $parts);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
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
