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
        $user = User::find(1);

        $task = new Task([
            'name' => 'zakupy',
            'completed' => true,
        ]);
        $user->tasks()->save($task);

        $task = new Task([
            'name' => 'xyz',
        ]);
        $user->tasks()->save($task);

        $task = new Task([
            'name' => 'zyx',
        ]);
        $user->tasks()->save($task);
    }
}
