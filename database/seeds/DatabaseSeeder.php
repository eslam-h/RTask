<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersSeederTable::class,
            ActivitiesTableSeeder::class,
            TagsTableSeeder::class,
            CityTableSeeder::class,
            CurrencyTableSeeder::class,
            TripsTableSeeder::class,
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            AdminRolePermissionsSeeder::class,
            SupplierRolePermissionsSeeder::class
        ]);
    }
}
