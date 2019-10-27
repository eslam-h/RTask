<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * UsersSeederTable Class insert default values to users table
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UsersSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("users")->insert([
            [
                "first-name" => "Shift",
                "email" => "admin@shiftebusiness.com",
                "password" => Hash::make("admin"),
                "is-active" => 1,
                "role-id" => 1,
            ],
            [
                "first-name" => "Supplier",
                "email" => "supplier@shiftebusiness.com",
                "password" => Hash::make("supplier"),
                "is-active" => 1,
                "role-id" => 3,
            ]
        ]);
    }
}