<?php

use Dev\Infrastructure\Models\ActivityModels\ActivityModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(ActivityModel::class, function (Faker $faker) {
    $imagesPaths = [
        "seeding-images/activities/images/adventure.jpg",
        "seeding-images/activities/images/camping.jpg",
        "seeding-images/activities/images/diving.jpg",
        "seeding-images/activities/images/fishing.jpg",
        "seeding-images/activities/images/museum.jpg",
        "seeding-images/activities/images/parasailing.jpg",
        "seeding-images/activities/images/safari.jpg",
        "seeding-images/activities/images/sea.jpg",
        "seeding-images/activities/images/snorkeling.jpg"
    ];
    $iconPaths = [
        "seeding-images/activities/icons/Icon1.svg",
        "seeding-images/activities/icons/Icon2.svg",
        "seeding-images/activities/icons/Icon3.svg",
        "seeding-images/activities/icons/Icon4.svg",
        "seeding-images/activities/icons/Icon5.svg",
        "seeding-images/activities/icons/Icon6.svg",
        "seeding-images/activities/icons/Icon7.svg",
        "seeding-images/activities/icons/Icon8.svg",
        "seeding-images/activities/icons/Icon9.svg"
    ];
    return [
        "name" => $faker->unique()->word,
        "icon" => $faker->randomElement($iconPaths),
        "photo" => $faker->randomElement($imagesPaths),
        "color" => $faker->hexColor
    ];
});