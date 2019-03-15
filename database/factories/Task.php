<?php

use App\Task;
use Faker\Generator as Faker;
use App\User;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'completed' => 0,
    ];
});
