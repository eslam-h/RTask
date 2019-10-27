<?php

use Dev\Infrastructure\Models\TripModels\TripRateModel;
use Illuminate\Database\Seeder;

class TripRateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TripRateModel::class, 100)->create();
    }
}
