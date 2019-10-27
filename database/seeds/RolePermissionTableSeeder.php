<?php

use Dev\Infrastructure\Models\PermissionsModels\PermissionModel;
use Dev\Infrastructure\Models\RolePermissionModels\RolePermissionModel;
use Dev\Infrastructure\Models\TripModels\TripRateModel;
use Illuminate\Database\Seeder;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionCount = PermissionModel::count();
        factory(RolePermissionModel::class, $permissionCount)->create();
    }
}
