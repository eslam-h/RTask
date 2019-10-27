<?php

use Dev\Infrastructure\Models\TripModels\TripModel;
use Dev\Infrastructure\Models\TripModels\TripWishListModel;
use Dev\Infrastructure\Models\UserModels\UserModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TripWishListModel::class, function (Faker $faker) {
	$tripIds = TripModel::pluck('id')->toArray();
    $userIds = UserModel::pluck('id')->toArray();
    return [
        "trip-id" => $faker->randomElement($tripIds),
        "user-id" => $faker->randomElement($userIds),
        "created-at" => $faker->dateTime(),
    ];
});
