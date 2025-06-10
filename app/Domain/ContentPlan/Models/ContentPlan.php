<?php

namespace App\Domain\ContentPlan\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPlan extends Model
{
    protected $table = 'content_plan';
    
    protected $fillable = [
        'created_date',
        'target_posting_date',
        'status',
        'objektif',
        'jenis_konten',
        'pillar',
        'sub_pillar',
        'talent',
        'venue',
        'hook',
        'produk',
        'referensi',
        'platform',
        'akun',
        'kerkun',
        'brief_konten',
        'caption',
        'link_raw_content',
        'assignee_content_editor',
        'link_hasil_edit',
        'input_link_posting',
        'posting_date',
    ];

    protected $casts = [
        'created_date' => 'date',
        'target_posting_date' => 'date',
        'posting_date' => 'datetime',
    ];

    // Status constants for workflow
    const STATUS_DRAFT = 'draft'; // Step 1: Social Media Strategist
    const STATUS_CONTENT_WRITING = 'content_writing'; // Step 2: Content Writer
    const STATUS_CREATIVE_REVIEW = 'creative_review'; // Step 3: Creative Leader
    const STATUS_ADMIN_SUPPORT = 'admin_support'; // Step 4: Admin Support
    const STATUS_CONTENT_EDITING = 'content_editing'; // Step 5: Content Editor
    const STATUS_READY_TO_POST = 'ready_to_post'; // Step 6: Admin Social Media
    const STATUS_POSTED = 'posted';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft (Social Media Strategist)',
            self::STATUS_CONTENT_WRITING => 'Content Writing',
            self::STATUS_CREATIVE_REVIEW => 'Creative Review',
            self::STATUS_ADMIN_SUPPORT => 'Admin Support',
            self::STATUS_CONTENT_EDITING => 'Content Editing',
            self::STATUS_READY_TO_POST => 'Ready to Post',
            self::STATUS_POSTED => 'Posted',
        ];
    }

    public function getStatusLabelAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? $this->status;
    }

    public function canEditByStep($step)
    {
        $allowedStatuses = [
            1 => [self::STATUS_DRAFT],
            2 => [self::STATUS_CONTENT_WRITING],
            3 => [self::STATUS_CREATIVE_REVIEW],
            4 => [self::STATUS_ADMIN_SUPPORT],
            5 => [self::STATUS_CONTENT_EDITING],
            6 => [self::STATUS_READY_TO_POST],
        ];

        return in_array($this->status, $allowedStatuses[$step] ?? []);
    }
}