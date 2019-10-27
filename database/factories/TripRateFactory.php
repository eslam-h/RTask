<?php

use Dev\Infrastructure\Models\TripModels\TripBookingModel;
use Dev\Infrastructure\Models\TripModels\TripEventModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Dev\Infrastructure\Models\TripModels\TripRateModel;
use Dev\Infrastructure\Models\UserModels\UserModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TripRateModel::class, function (Faker $faker) {
	$tripIds = TripModel::pluck('id')->toArray();
    $userIds = UserModel::pluck('id')->toArray();
    return [
        "trip-id" => $faker->randomElement($tripIds),
        "user-id" => $faker->randomElement($userIds),
        "rate" => rand(1,6)
    ];
});
