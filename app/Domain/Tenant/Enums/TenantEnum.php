<?php

namespace App\Domain\Tenant\Enums;

enum TenantEnum
{
    const Active = 'active';

    const Inactive = 'inactive';

    const Status = [
        self::Active,
        self::Inactive,
    ];
}
