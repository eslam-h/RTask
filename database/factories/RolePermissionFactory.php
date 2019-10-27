<?php

use Dev\Infrastructure\Models\PermissionsModels\PermissionModel;
use Dev\Infrastructure\Models\RolePermissionModels\RolePermissionModel;

use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(RolePermissionModel::class, function (Faker $faker) {
	$permissionIds = PermissionModel::pluck('id')->toArray();

    return [
        "role-id" => '3',
        "permission-id" => $faker->unique()->randomElement($permissionIds),
    ];
});
