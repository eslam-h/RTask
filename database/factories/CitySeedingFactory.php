<?php

use Faker\Generator as Faker;
use Dev\Infrastructure\Models\CityModels\CityModel;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(CityModel::class, function (Faker $faker) {
    $imagesPaths = [
        "seeding-images/cities/images/3dkCfoBvOE1nZhEa1WVUq0yFo7n96XQEydCIsRz9.jpeg",
        "seeding-images/cities/images/200097209.jpg",
        "seeding-images/cities/images/Austria-Travel-Photo-Gallery.jpg",
        "seeding-images/cities/images/bostondowntown.jpg",
        "seeding-images/cities/images/FbsP9G7p5Di0ZA8MmSY2ZmAjwJyLAgIUFEZXv1sk.jpeg",
        "seeding-images/cities/images/gFZXTJkwWRt4cYjfj2TSIj0RroIBE0hwbwY50cks.jpeg",
        "seeding-images/cities/images/oPzsSaiF9yu0FkJPDL0zZMWSrPTKGrxxbWpnX3Lw.jpeg",
        "seeding-images/cities/images/photo-1532455935509-eb76842cee50.jpeg",
        "seeding-images/cities/images/qN4S9VCeri4eSlsef1mTVMbDADZq0Ui6qWWmQnPk.jpeg",
        "seeding-images/cities/images/urban-drone-photography-19.jpg",
        "seeding-images/cities/images/X4dWLnmiuco5ELdN3284jsWpbHx06FX2h4CzeRWw.jpeg"
    ];
    return [
        "country-id" => 1,
        'name' => $faker->word,
        "image-path" => $faker->randomElement($imagesPaths)
    ];
});