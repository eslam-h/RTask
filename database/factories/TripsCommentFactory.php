<?php

use Dev\Infrastructure\Models\TripModels\TripBookingModel;
use Dev\Infrastructure\Models\TripModels\TripCommentModel;
use Dev\Infrastructure\Models\TripModels\TripEventModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Dev\Infrastructure\Models\TripModels\TripRateModel;
use Dev\Infrastructure\Models\UserModels\UserModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TripCommentModel::class, function (Faker $faker) {
	$tripIds = TripRateModel::pluck('trip-id')->toArray();
    $userIds = TripRateModel::pluck('user-id')->toArray();
    return [
        "trip-id" => $faker->randomElement($tripIds),
        "user-id" => $faker->randomElement($userIds),
        "comment" => $faker->sentence,
        "is-approved" => rand(1,0)
    ];
});
