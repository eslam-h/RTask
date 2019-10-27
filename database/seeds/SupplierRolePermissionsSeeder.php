<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SupplierRolePermissionsSeeder Class responsible for adding default permissions for supplier role
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class SupplierRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role-permission')->insert([
            ['role-id' => 3, 'permission-id' => 32],
            ['role-id' => 3, 'permission-id' => 33],
            ['role-id' => 3, 'permission-id' => 35],
            ['role-id' => 3, 'permission-id' => 37],
            ['role-id' => 3, 'permission-id' => 38],
            ['role-id' => 3, 'permission-id' => 39],
            ['role-id' => 3, 'permission-id' => 40],
            ['role-id' => 3, 'permission-id' => 41]
        ]);
    }
}