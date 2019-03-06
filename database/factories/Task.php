<?php

use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'completed' => 0,
        'user_id' => $faker->randomDigit
    ];
});
