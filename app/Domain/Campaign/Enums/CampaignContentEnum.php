<?php

namespace App\Domain\Campaign\Enums;

enum CampaignContentEnum
{
    // Status
    const InstagramFeed = 'instagram_feed';
    const InstagramStory = 'instagram_story';
    const TiktokVideo = 'tiktok_video';
    const TikTokLive = 'tiktok_live';
    const YoutubeVideo = 'youtube_video';
    const TwitterPost = 'twitter_post';

    const Platform = [
        [
            'value' => self::InstagramFeed,
            'label' => self::InstagramFeed . ' (Auto)',
        ],
        [
            'value' => self::InstagramStory,
            'label' => self::InstagramStory . ' (Manual)',
        ],
        [
            'value' => self::TiktokVideo,
            'label' => self::TiktokVideo . ' (Auto)',
        ],
        [
            'value' => self::TikTokLive,
            'label' => self::TikTokLive . ' (Manual)',
        ],
        [
            'value' => self::YoutubeVideo,
            'label' => self::YoutubeVideo . ' (Manual)',
        ],
        [
            'value' => self::TwitterPost,
            'label' => self::TwitterPost. ' (Auto)',
        ],
    ];

    const PlatformValidation = [
        self::InstagramFeed,
        self::InstagramStory,
        self::TiktokVideo,
        self::TikTokLive,
        self::YoutubeVideo,
        self::TwitterPost,
    ];
}
