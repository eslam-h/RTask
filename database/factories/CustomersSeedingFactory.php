<?php

use Dev\Infrastructure\Models\CustomerModels\CustomerModel;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(CustomerModel::class, function (Faker $faker) {
    return [
        "name" => "Tripasus",
        "access-token" => "4AD2B295C423464D5F2E77B632F24",
        "is-active" => 1,
        "is-deleted" => 0
    ];
});