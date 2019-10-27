<?php

use Dev\Infrastructure\Models\CityModels\CityModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Dev\Infrastructure\Models\UserModels\UserModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(\Dev\Infrastructure\Models\TripModels\RecentViewedTripsModel::class, function (Faker $faker) {
	$tripIds = TripModel::pluck('id')->toArray();
    $userIds = UserModel::pluck('id')->toArray();
    $cityIds = CityModel::pluck('id')->toArray();
    return [
        "trip-id" => $faker->randomElement($tripIds),
        "user-id" => $faker->randomElement($userIds),
        "city-id" => $faker->randomElement($cityIds),
        "viewed-at" => $faker->dateTime()
    ];
});
