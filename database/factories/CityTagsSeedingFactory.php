<?php

use Dev\Infrastructure\Models\CityModels\CityTagModel;
use Dev\Infrastructure\Models\TagModels\TagModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(CityTagModel::class, function (Faker $faker) {
    return [
        "tag-id" => function() {
            return TagModel::select("id")->inRandomOrder()->first();
        }
    ];
});