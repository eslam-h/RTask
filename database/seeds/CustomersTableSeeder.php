<?php

use Dev\Infrastructure\Models\CustomerModels\CustomerModel;
use Illuminate\Database\Seeder;

/**
 * CustomersTableSeeder Class responsible for cutomers table seeding
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(CustomerModel::class, 1)->create();
    }
}