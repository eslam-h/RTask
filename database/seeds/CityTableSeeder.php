<?php

use Illuminate\Database\Seeder;
use Dev\Infrastructure\Models\CityModels\CityModel;
use Dev\Infrastructure\Models\CityModels\CityTranslationModel;
use Dev\Infrastructure\Models\CityModels\CityTagModel;

/**
 * Class CityTableSeeder
 */
class CityTableSeeder extends seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        factory(CityModel::class, 20)->create()
            ->each(function ($city) {
                $city->cityTranslation()->save(factory(CityTranslationModel::class)->create(
                    [
                        "city-id" => $city->id
                    ]
                ));
                $city->cityTags()->save(factory(CityTagModel::class)->create(
                    [
                        "city-id" => $city->id
                    ]
                ));
            });
    }
}