<?php

namespace App\Domain\Marketing\Enums;

enum MarketingCategoryTypeEnum
{
    const Marketing = 'marketing';

    const Branding = 'branding';

    const Category = [
        self::Marketing,
        self::Branding,
    ];

    const AllMarketingCategoryCacheTag = 'all_marketing_categories';

    const AllMarketingSubCategoryCacheTag = 'all_marketing_sub_categories';

    public static function getDescription($value): string
    {
        return match ($value) {
            self::Marketing => 'marketing',
            self::Branding => 'branding',
            default => 'Unknown'
        };
    }
}
