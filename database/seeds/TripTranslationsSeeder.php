<?php

use Illuminate\Database\Seeder;
use Dev\Infrastructure\Models\TripModels\TripTranslationModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
/**
 * TripTranslationsSeeder Class generate dummy data for trip translation
 */
class TripTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $tripsCount = TripModel::count();
        factory(TripTranslationModel::class, $tripsCount)->create();
    }
}