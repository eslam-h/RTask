<?php

use Faker\Generator as Faker;
use Dev\Infrastructure\Models\CityModels\CityTranslationModel;
use Dev\Infrastructure\Models\SystemLanguageModel\SystemAvailableLanguageModel;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(CityTranslationModel::class, function (Faker $faker) {
    return [
        "name" => $faker->words(1, true),
        "language-code" => function() {
            return SystemAvailableLanguageModel::select("code")->inRandomOrder()->first();
        },
    ];
});