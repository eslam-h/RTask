<?php

use Dev\Infrastructure\Models\TripModels\TripTranslationModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Faker\Generator as Faker;
use Dev\Infrastructure\Models\SystemLanguageModel\SystemAvailableLanguageModel;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TripTranslationModel::class, function (Faker $faker) {
    return [
        "trip-id" => function() {
            return TripModel::select("id")->inRandomOrder()->first();
        },
        "display-name" => $faker->name,
        "description" => $faker->text,
        "package-options" => $faker->text,
        "inclusive-of" => $faker->text,
        "not-inclusive-of" => $faker->text,
        "meet-up-info" => $faker->text,
        "cancellation-policy" => $faker->text,
        "trip-available-language-code" => function() {
            return SystemAvailableLanguageModel::select("code")->inRandomOrder()->first();
        }
    ];
});