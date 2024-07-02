<?php

namespace App\Domain\User\Enums;

enum RoleEnum: string
{
    const SuperAdmin = 'superadmin';

    const Marketing = 'marketing';

    const Finance = 'finance';

    const HR = 'hr';

    const BrandManager = 'brand manager';

    const Staff = 'staff';

    public static function getDescription($value): string
    {
        return match ($value) {
            self::SuperAdmin => 'SuperAdmin',
            self::BrandManager => 'Brand Manager',
            self::Marketing => 'Marketing',
            self::Finance => 'Finance',
            self::HR => 'HR',
            self::Staff => 'Staff',
            default => 'Unknown'
        };
    }
}
