<?php

namespace App\Domain\Funnel\Enums;

enum FunnelTypeEnum
{
    const TOFU = 'tofu';

    const MOFU = 'mofu';

    const BOFU = 'bofu';

    const Types = [
        self::TOFU,
        self::MOFU,
        self::BOFU,
    ];
}
