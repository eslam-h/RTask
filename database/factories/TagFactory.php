<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */


use Faker\Generator as Faker;
use Dev\Infrastructure\Models\TagModels\TagModel;

$factory->define(TagModel::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});
