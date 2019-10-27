<?php

use Dev\Infrastructure\Models\TripModels\TripBookingModel;
use Illuminate\Database\Seeder;

class TripBookingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TripBookingModel::class, 100)->create();
    }
}
