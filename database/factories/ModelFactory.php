<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(smarthome\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => str_random(10),
        'remember_token' => str_random(10),
        'role' => rand(0, 3),
    ];
});

$factory->define(smarthome\Device::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(8),
        'infrared' => rand(0,1) == 1,
        'status' => rand(0,1) == 1,
    ];
});

$factory->define(smarthome\Message::class, function (Faker\Generator $faker) {
    return [
        'level' => rand(0,3),
        'title' => str_random(20),
        'content' => str_random(1024),
        'from' => str_random(10),
        'read' => rand(0,1) == 1,
    ];
});

$factory->define(smarthome\Room::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(7),
        'floor' => rand(1,3),
    ];
});

$factory->define(smarthome\Scene::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(5),
    ];
});

