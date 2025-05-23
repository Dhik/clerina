<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Campaign\Enums\KeyOpinionLeaderEnum;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeyOpinionLeader extends Model
{

    protected $fillable = [
        'channel',
        'username',
        'niche',
        'average_view',
        'skin_type',
        'skin_concern',
        'content_type',
        'rate',
        'pic_contact',
        'created_by',
        'cpm',
        'name',
        'address',
        'phone_number',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'npwp',
        'npwp_number',
        'nik',
        'notes',
        'product_delivery',
        'product',
        'followers',
        'following',
        'engagement_rate',
        'program',
        'views_last_9_post',
        'activity_posting',
        'status_affiliate',
        'total_likes',
        'video_count',
    ];

    protected $casts = [
        'views_last_9_post' => 'boolean',
        'activity_posting' => 'boolean',
        'engagement_rate' => 'float',
    ];

    protected $appends = [
        'social_media_link',
        'wa_link'
    ];

    /**
     * Return social media link
     */
    public function getSocialMediaLinkAttribute(): ?string
    {
        $channel = $this->channel;
        $username = $this->username;

        if ($channel === KeyOpinionLeaderEnum::Tiktok) {
            return 'https://www.tiktok.com/@' . $username;
        }

        if ($channel === KeyOpinionLeaderEnum::Instagram) {
            return 'https://www.instagram.com/' . $username;
        }

        if ($channel === KeyOpinionLeaderEnum::Youtube) {
            return 'https://www.youtube.com/' . $username;
        }

        if ($channel === KeyOpinionLeaderEnum::Twitter) {
            return 'https://twitter.com/' . $username;
        }

        return null;
    }

    /**
     * Return link WhatsApp
     */
    public function getWaLinkAttribute(): string
    {
        $phoneNumber = $this->phone_number;

        // Check if phone number starts with '0'
        if (str_starts_with($phoneNumber, '0')) {
            // Replace '0' with '62'
            $phoneNumber = '62' . substr($phoneNumber, 1);
        }

        return 'https://wa.me/' . $phoneNumber;
    }

    public function picContact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_contact')->withoutGlobalScopes();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}