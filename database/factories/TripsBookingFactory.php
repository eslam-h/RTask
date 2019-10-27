<?php

use Dev\Infrastructure\Models\TripModels\TripBookingModel;
use Dev\Infrastructure\Models\TripModels\TripEventModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Dev\Infrastructure\Models\UserModels\UserModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TripBookingModel::class, function (Faker $faker) {
	$tripIds = TripModel::pluck('id')->toArray();
    $eventIds = TripEventModel::pluck('id')->toArray();
    $userIds = UserModel::pluck('id')->toArray();
    return [
        "trip-id" => $faker->randomElement($tripIds),
        "event-id" => $faker->randomElement($eventIds),
        "user-id" => $faker->randomElement($userIds),
        'number-of-adults' => rand(1,5),
        'number-of-childs' => rand(1,9),
        'total-price' => rand(1000,9999),
        'confirmed' => rand(1,0),
    ];
});
