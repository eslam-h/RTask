<?php

use Faker\Generator as Faker;
use Dev\Infrastructure\Models\TagModels\TagTranslationModel;
use Dev\Infrastructure\Models\TagModels\TagModel;
use Dev\Infrastructure\Models\SystemLanguageModel\SystemAvailableLanguageModel;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TagTranslationModel::class, function (Faker $faker) {
    return [
        "tag-id" => function() {
            return factory(TagModel::class)->create()->id;
        },
        "name" => $faker->words(1, true),
        "language-code" => function() {
            return SystemAvailableLanguageModel::select("code")->inRandomOrder()->first();
        }
    ];
});
