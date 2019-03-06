<?php

namespace Tests\Feature\Task;

use App\Task;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_tasks()
    {
        $user = factory(User::class)->create();
        $numberOfTasks = 3;
        for ($i = 0; $i < $numberOfTasks; $i++) {
            factory(Task::class)->create([
                'user_id' => $user->id
            ]);
        }

        $response = $this->actingAs($user)->get('/tasks');
        $tasks = $response->getOriginalContent()->getData();

        $response->assertSuccessful();
        $response->assertViewIs('tasks.list');
        $response->assertViewHas('tasks');
        $response->assertViewHasAll($tasks);
    }

    public function test_user_cannot_see_task_not_belonging_to_him()
    {
        $firstUser = factory(User::class)->create();
        $secondUser = factory(User::class)->create();
        $numberOfTasks = 3;
        for ($i = 0; $i < $numberOfTasks; $i++) {
            factory(Task::class)->create([
                'user_id' => $firstUser->id
            ]);
            factory(Task::class)->create([
                'user_id' => $secondUser->id
            ]);
        }

        $responseOfFirstUser = $this->actingAs($firstUser)->get('/tasks');
        $responseOfSecondUser = $this->actingAs($secondUser)->get('/tasks');
        $tasksOfFirstUser = $responseOfFirstUser->getOriginalContent()->getData();
        $tasksOfSecondUser = $responseOfSecondUser->getOriginalContent()->getData();

        $responseOfFirstUser->assertSuccessful();
        $responseOfFirstUser->assertViewIs('tasks.list');
        $responseOfFirstUser->assertViewHas('tasks');
        $responseOfFirstUser->assertViewHasAll($tasksOfFirstUser);
        foreach ($tasksOfSecondUser['tasks']as $task) {
            $responseOfFirstUser->assertDontSeeText($task->name);
        }
    }

    public function test_user_can_add_new_task()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->followingRedirects()->post('/tasks');
        $tasks = $response->getOriginalContent()->getData();

        $response->assertViewHas('tasks');
        $response->assertViewHasAll($tasks);
    }

    public function test_user_can_update_task_name()
    {
        $user = factory(User::class)->create();
        $numberOfTasks = 5;
        $taskToUpdate = [
            'name' => 'updatedTask'
        ];
        $tasks = [];
        for ($i = 0; $i < $numberOfTasks; $i++) {
            array_push(
                $tasks,
                factory(Task::class)->create([
                    'user_id' => $user->id
                ])
            );
        }
        $randomTaskId = $tasks[rand(0, $numberOfTasks - 1)]['id'];

        $response = $this->actingAs($user)->patch('/api/tasks/update/column/' . $randomTaskId, $taskToUpdate);

        $response->assertExactJson(['status' => true, 'updated' => true]);
    }

    public function test_user_can_update_task_status()
    {
        $user = factory(User::class)->create();
        $numberOfTasks = 5;
        $tasks = [];
        for ($i = 0; $i < $numberOfTasks; $i++) {
            array_push(
                $tasks,
                factory(Task::class)->create([
                    'user_id' => $user->id
                ])
            );
        }
        $taskToCheck = $tasks[rand(0, $numberOfTasks - 1)];

        $response = $this->actingAs($user)->patch('/api/tasks/complete/' . $taskToCheck->id);
        $response->assertExactJson(['status' => true, 'completed' => !$taskToCheck->completed]);
    }

    public function user_cannot_update_task_not_belonging_to_him()
    {
        $firstUser = factory(User::class)->create();
        $secondUser = factory(User::class)->create();
        $numberOfTasks = 5;
        $taskToUpdate = [
            'name' => 'updatedTask'
        ];
        $tasksOfFirstUser = [];
        $tasksOfSecondUser = [];
        for ($i = 0; $i < $numberOfTasks; $i++) {
            array_push(
                $tasksOfFirstUser,
                factory(Task::class)->create([
                    'user_id' => $firstUser->id
                ])
            );
            array_push(
                $tasksOfSecondUser,
                factory(Task::class)->create([
                    'user_id' => $secondUser->id
                ])
            );
        }
        $randomTaskOfFirstUser = $tasksOfFirstUser[rand(0, $numberOfTasks - 1)];

        $response = $this->actingAs($secondUser)->patch('/api/tasks/update/column/' . $randomTaskOfFirstUser->id, $taskToUpdate);

        $response->assertForbidden();
        $response->assertExactJson(['status' => 'error', 'error' => 'Forbidden!']);
    }

    public function test_user_cannot_update_task_which_does_not_exist()
    {
        $firstUser = factory(User::class)->create();
        $numberOfTasks = 5;
        $taskToUpdate = [
            'name' => 'updatedTask'
        ];
        $tasksOfFirstUser = [];
        for ($i = 0; $i < $numberOfTasks; $i++) {
            array_push(
                $tasksOfFirstUser,
                factory(Task::class)->create([
                    'user_id' => $firstUser->id
                ])
            );
        }

        $response = $this->actingAs($firstUser)->patch('/api/tasks/update/column/' . 9999999, $taskToUpdate);

        $response->assertNotFound();
    }

    public function test_user_can_delete_task()
    {
        $firstUser = factory(User::class)->create();
        $numberOfTasks = 5;
        for ($i = 0; $i < $numberOfTasks; $i++) {
            factory(Task::class)->create([
                'user_id' => $firstUser->id
            ]);
        }

        $responseOfFirstUser = $this->actingAs($firstUser)->get('/tasks');
        $tasksOfFirstUser = $responseOfFirstUser->getOriginalContent()->getData();
        $checkNumberOfTaskBeforeDeletion = count($tasksOfFirstUser['tasks']);
        $randomTaskOfFirstUser = $tasksOfFirstUser['tasks'][rand(0, $numberOfTasks - 1)];

        $response = $this->actingAs($firstUser)->delete('/api/tasks/' . $randomTaskOfFirstUser->id);
        $responseOfFirstUser = $this->actingAs($firstUser)->get('/tasks');
        $tasksOfFirstUser = $responseOfFirstUser->getOriginalContent()->getData();
        $checkNumberOfTaskAfterDeletion = count($tasksOfFirstUser['tasks']);

        $response->assertOk();
        $response->assertExactJson(['status' => true, 'deleted' => true]);
        $this->assertNotEquals($checkNumberOfTaskAfterDeletion, $checkNumberOfTaskBeforeDeletion);
    }

    public function test_user_cannot_delete_task_not_belonging_to_him()
    {
        $firstUser = factory(User::class)->create();
        $secondUser = factory(User::class)->create();
        $numberOfTasks = 5;
        $tasksOfFirstUser = [];
        $tasksOfSecondUser = [];
        for ($i = 0; $i < $numberOfTasks; $i++) {
            array_push(
                $tasksOfFirstUser,
                factory(Task::class)->create([
                    'user_id' => $firstUser->id
                ])
            );
            array_push(
                $tasksOfSecondUser,
                factory(Task::class)->create([
                    'user_id' => $secondUser->id
                ])
            );
        }
        $randomTaskOfFirstUser = $tasksOfFirstUser[rand(0, $numberOfTasks - 1)];

        $response = $this->actingAs($secondUser)->delete('/api/tasks/' . $randomTaskOfFirstUser->id);

        $response->assertForbidden();
        $response->assertExactJson(['status' => 'error', 'error' => 'Forbidden!']);
    }

    public function test_user_cannot_delete_task_which_does_not_exist()
    {
        $firstUser = factory(User::class)->create();
        $numberOfTasks = 5;
        $tasksOfFirstUser = [];
        for ($i = 0; $i < $numberOfTasks; $i++) {
            array_push(
                $tasksOfFirstUser,
                factory(Task::class)->create([
                    'user_id' => $firstUser->id
                ])
            );
        }

        $response = $this->actingAs($firstUser)->delete('/api/tasks/' . 9999999);

        $response->assertNotFound();
    }
}
