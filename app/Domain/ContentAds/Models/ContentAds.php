<?php

namespace App\Domain\ContentAds\Models;

use Illuminate\Database\Eloquent\Model;

class ContentAds extends Model
{
    protected $table = 'content_ads';
    
    protected $fillable = [
        'link_ref',
        'desc_request',
        'product',
        'platform',
        'funneling',
        'request_date',
        'link_drive',
        'editor',
        'status',
        'filename',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];

    // Status constants for workflow
    const STATUS_STEP1 = 'step1'; // Step 1: Initial Request
    const STATUS_STEP2 = 'step2'; // Step 2: Link Drive & Task Completion
    const STATUS_STEP3 = 'step3'; // Step 3: File Naming
    const STATUS_COMPLETED = 'completed';

    // Product options
    const PRODUCTS = [
        'CLE-XFO-008' => 'CLE-XFO-008',
        'CLE-JB30-001' => 'CLE-JB30-001',
        'CLE-CLNDLA-025' => 'CLE-CLNDLA-025',
        'CLE-RS-047' => 'CLE-RS-047',
        'CL-GS' => 'CL-GS',
        'CL-TNR' => 'CL-TNR',
        'CLE-NEG-071' => 'CLE-NEG-071',
        'CLE-ASG-059' => 'CLE-ASG-059',
        'CL-JBRS' => 'CL-JBRS',
        'CLE-BD-XFOJB30-017' => 'CLE-BD-XFOJB30-017',
        'None' => 'None',
    ];

    // Platform options
    const PLATFORMS = [
        'META' => 'META',
        'TIKTOK' => 'TIKTOK',
    ];

    // Funneling options
    const FUNNELINGS = [
        'TOFU' => 'TOFU',
        'MOFU' => 'MOFU',
        'BOFU' => 'BOFU',
        'None' => 'None',
    ];

    // Editor options
    const EDITORS = [
        'RAFI' => 'RAFI',
        'HENDRA' => 'HENDRA',
    ];

    public static function getStatusOptions()
    {
        return [
            self::STATUS_STEP1 => 'Step 1: Initial Request',
            self::STATUS_STEP2 => 'Step 2: Link Drive & Task',
            self::STATUS_STEP3 => 'Step 3: File Naming',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    public static function getProductOptions()
    {
        return self::PRODUCTS;
    }

    public static function getPlatformOptions()
    {
        return self::PLATFORMS;
    }

    public static function getFunnelingOptions()
    {
        return self::FUNNELINGS;
    }

    public static function getEditorOptions()
    {
        return self::EDITORS;
    }

    public function getStatusLabelAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? $this->status;
    }

    public function canEditByStep($step)
    {
        $allowedStatuses = [
            1 => [self::STATUS_STEP1],
            2 => [self::STATUS_STEP2],
            3 => [self::STATUS_STEP3],
        ];

        return in_array($this->status, $allowedStatuses[$step] ?? []);
    }

    // Relationships
    // No relationships needed for simplified version

    // Scopes for reporting
    public function scopeByProduct($query, $product)
    {
        return $query->where('product', $product);
    }

    public function scopeByFunneling($query, $funneling)
    {
        return $query->where('funneling', $funneling);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByEditor($query, $editor)
    {
        return $query->where('editor', $editor);
    }
}