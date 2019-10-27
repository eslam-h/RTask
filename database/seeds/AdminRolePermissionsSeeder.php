<?php

use Dev\Infrastructure\Models\CustomerModels\CustomerModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AdminRolePermissionsSeeder Class responsible for adding default permissions for admin role
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class AdminRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role-permission')->insert([
            ['role-id' => 1, 'permission-id' => 1],
            ['role-id' => 1, 'permission-id' => 2],
            ['role-id' => 1, 'permission-id' => 3],
            ['role-id' => 1, 'permission-id' => 4],
            ['role-id' => 1, 'permission-id' => 5],
            ['role-id' => 1, 'permission-id' => 6],
            ['role-id' => 1, 'permission-id' => 7],
            ['role-id' => 1, 'permission-id' => 8],
            ['role-id' => 1, 'permission-id' => 9],
            ['role-id' => 1, 'permission-id' => 10],
            ['role-id' => 1, 'permission-id' => 11],
            ['role-id' => 1, 'permission-id' => 12],
            ['role-id' => 1, 'permission-id' => 13],
            ['role-id' => 1, 'permission-id' => 14],
            ['role-id' => 1, 'permission-id' => 15],
            ['role-id' => 1, 'permission-id' => 16],
            ['role-id' => 1, 'permission-id' => 17],
            ['role-id' => 1, 'permission-id' => 18],
            ['role-id' => 1, 'permission-id' => 19],
            ['role-id' => 1, 'permission-id' => 20],
            ['role-id' => 1, 'permission-id' => 21],
            ['role-id' => 1, 'permission-id' => 22],
            ['role-id' => 1, 'permission-id' => 23],
            ['role-id' => 1, 'permission-id' => 24],
            ['role-id' => 1, 'permission-id' => 25],
            ['role-id' => 1, 'permission-id' => 26],
            ['role-id' => 1, 'permission-id' => 27],
            ['role-id' => 1, 'permission-id' => 28],
            ['role-id' => 1, 'permission-id' => 29],
            ['role-id' => 1, 'permission-id' => 30],
            ['role-id' => 1, 'permission-id' => 31],
            ['role-id' => 1, 'permission-id' => 34],
            ['role-id' => 1, 'permission-id' => 36],
            ['role-id' => 1, 'permission-id' => 38],
            ['role-id' => 1, 'permission-id' => 41],
            ['role-id' => 1, 'permission-id' => 42],
            ['role-id' => 1, 'permission-id' => 43],
            ['role-id' => 1, 'permission-id' => 44],
            ['role-id' => 1, 'permission-id' => 45],
            ['role-id' => 1, 'permission-id' => 46],
            ['role-id' => 1, 'permission-id' => 47],
        ]);
    }
}