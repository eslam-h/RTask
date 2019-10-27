<?php

use Dev\Domain\Utility\ConfirmationType;
use Dev\Infrastructure\Models\ActivityModels\ActivityModel;
use Dev\Infrastructure\Models\CityModels\CityModel;
use Dev\Infrastructure\Models\CurrencyModels\CurrencyModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Faker\Generator as Faker;
use App\Utility\UploadPaths;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(TripModel::class, function (Faker $faker) {
    $imagesPaths = [
        "seeding-images/trips/images/1fa9e19ddbf290072445f49ee7a9b7cc.jpg",
        "seeding-images/trips/images/20130512-01-a-beach-cotttage-blog-coastal-vintage-style-Australia-abeachcottage.com_.jpg",
        "seeding-images/trips/images/1491584555462.jpeg",
        "seeding-images/trips/images/1523383408931.jpeg",
        "seeding-images/trips/images/beaches_to_visit_cayo_largo.jpg",
        "seeding-images/trips/images/Beach-Trip-Tips-768x512.jpg",
        "seeding-images/trips/images/bJ1o_Onj-1380x1035.jpeg",
        "seeding-images/trips/images/ClearwaterBeach-59cd9d74685fbe0011577afd.jpg",
        "seeding-images/trips/images/da.jpg",
        "seeding-images/trips/images/default.jpg",
        "seeding-images/trips/images/f3-980x784.jpg",
        "seeding-images/trips/images/GettyImages-183559345_full_1-0409c9dc0a3c.jpg",
        "seeding-images/trips/images/Highlight-St.-Barts-Image.jpg",
        "seeding-images/trips/images/landing-banner-punta-cana.jpg",
        "seeding-images/trips/images/premium-speed-boat.jpg",
        "seeding-images/trips/images/Railay-Beach-Krabi-2-1170x508.jpg",
        "seeding-images/trips/images/solråd.jpg",
        "seeding-images/trips/images/xbeach-palapa700.jpeg.pagespeed.ic.nX2n5lSZDh.jpg",
    ];
    return [
        "name" => $faker->name,
        "photo" => $faker->randomElement($imagesPaths),
        "city-id" => function() {
            return CityModel::select("id")->inRandomOrder()->first();
        },
        "price" => $faker->randomFloat(),
        "currency-id"  => function() {
            return CurrencyModel::select("id")->inRandomOrder()->first();
        },
        "activity-id" => function() {
            return ActivityModel::select("id")->inRandomOrder()->first();
        },
        "confirmation-type" => ConfirmationType::INSTANT_CONFIRMATION_ENUM_VALUE,
        "start-date" => $faker->date(),
        "start-time" => $faker->time(),
        "end-date" => $faker->date(),
        "end-time" => $faker->time()
    ];
});