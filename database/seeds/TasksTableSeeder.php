<?php

use App\User;
use App\Task;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();
        $task = new Task([
            'name' => 'See newest Marvel movie',
            'completed' => true,
        ]);
        $user->tasks()->save($task);

        $task = new Task([
            'name' => 'Go to the Gym',
        ]);
        $user->tasks()->save($task);

        $task = new Task([
            'name' => 'Buy potatoes and tomatoes',
        ]);
        $user->tasks()->save($task);
    }
}
