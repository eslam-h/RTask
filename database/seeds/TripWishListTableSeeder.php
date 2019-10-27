<?php

use Dev\Infrastructure\Models\TripModels\TripWishListModel;
use Illuminate\Database\Seeder;

class TripWishListTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        factory(TripWishListModel::class, 100)->create();
    }
}
