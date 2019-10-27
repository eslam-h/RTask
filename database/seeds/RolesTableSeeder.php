<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * RolesTableSeeder Class insert default values to roles table
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds
     * @return void
     */
    public function run()
    {
        DB::table("roles")->insert([
            [
                "id" => 1,
                "role" => "admin",
                "related-platforms" => "1"
            ],
            [
                "id" => 2,
                "role" => "visitor",
                "related-platforms" => "2"
            ],
            [
                "id" => 3,
                "role" => "supplier",
                "related-platforms" => "1,3"
            ]
        ]);
    }
}