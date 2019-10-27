<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Add default values to platforms table
 * PlatformsTableSeeder Class insert default records in platforms table
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class PlatformsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("platforms")->insert([
            [
                "name" => "Web Application",
                "code" => "WEB-APP"
            ],
            [
                "name" => "Visitor Mobile Application",
                "code" => "VM-APP"
            ],
            [
                "name" => "Supplier Mobile Application",
                "code" => "SM-APP"
            ]
        ]);
    }
}
