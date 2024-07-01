<?php

namespace Database\Seeders;

use App\Domain\User\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::updateOrcreate(['name' => RoleEnum::SuperAdmin]);
        Role::updateOrcreate(['name' => RoleEnum::BrandManager]);
        Role::updateOrcreate(['name' => RoleEnum::Marketing]);
        Role::updateOrcreate(['name' => RoleEnum::Finance]);
        Role::updateOrcreate(['name' => RoleEnum::HR]);
        Role::updateOrcreate(['name' => RoleEnum::Staff]);
    }
}
