<?php

use Dev\Infrastructure\Models\TripModels\RecentViewedTripsModel;
use Illuminate\Database\Seeder;

class RecentlyViewedTripsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(RecentViewedTripsModel::class, 100)->create();
    }
}
