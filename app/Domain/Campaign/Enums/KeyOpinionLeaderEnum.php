<?php

namespace App\Domain\Campaign\Enums;

enum KeyOpinionLeaderEnum
{
    // Channel
    const Tiktok = 'tiktok';
    const Instagram = 'instagram';
    const Youtube = 'youtube';
    const Twitter = 'twitter';

    // Niche
    const Beauty = 'beauty';
    const Random = 'random';

    // Skin Type
    const Combination = 'combination';
    const Normal = 'normal';
    const Dry = 'dry';
    const Oily = 'oily';

    // Skin concern
    const Jerawat = 'jerawat';
    const Kusam = 'kusam';
    const Sensitif = 'sensitif';

    // Content type
    const Reels = 'reels';
    const Partnership = 'partnership';
    const VT = 'vt';
    const Story = 'story';
    const ReelsPP = 'reels PP';
    const BA = 'ba';

    const Channel = [
        self::Tiktok,
        self::Instagram,
        self::Youtube,
        self::Twitter
    ];

    const Niche = [
        self::Beauty,
        self::Random
    ];

    const SkinType = [
        self::Combination,
        self::Normal,
        self::Dry,
        self::Oily
    ];

    const SkinConcern = [
        self::Jerawat,
        self::Kusam,
        self::Sensitif,
        self::Normal
    ];

    const ContentType = [
        self::Reels,
        self::Partnership,
        self::VT,
        self::Story,
        self::ReelsPP,
        self::BA
    ];
}
