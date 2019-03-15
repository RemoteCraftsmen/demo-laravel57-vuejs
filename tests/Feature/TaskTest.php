<?php

namespace Tests\Feature;

use App\Task;
use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_tasks()
    {
        $user = factory(User::class)->create();
        $tasks = factory(Task::class, 3)->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->get('tasks');

        $response
            ->assertSuccessful()
            ->assertViewIs('tasks.list')
            ->assertViewHas('tasks');

        $responseData = $response->getOriginalContent()->getData();

        $user->refresh();

        $this->assertEmpty(
            $user->tasks->diff($responseData['tasks'])
        );

        $this->assertEquals($user->tasks->count(), 3);
    }

    public function test_user_cannot_see_task_not_belonging_to_him()
    {
        [$john, $jane] = factory(User::class, 2)->create();

        factory(Task::class)->create([
            'user_id' => $john->id
        ]);

        factory(Task::class)->create([
            'user_id' => $jane->id
        ]);

        $response = $this->actingAs($john)->get('/tasks');
        $response->assertViewIs('tasks.list');
        $response->assertViewHas('tasks');
        
        $responseData = $response->getOriginalContent()->getData();

        $jane->refresh()->tasks->each(function($task) use ($responseData){
            $this->assertFalse(
                $responseData['tasks']->contains($task)
            );
        });
    }

    public function test_user_can_add_new_task()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/api/tasks', ['name' => 'test']);
        $response->assertJson([
            'task' => $user->tasks[0]->toArray()
        ]);
        
        $this->assertEquals($user->tasks->count(), 1);
    }

    public function test_user_can_update_task_name()
    {
        $user = factory(User::class)->create();

        $taskToUpdate = [
            'name' => 'updatedTask'
        ];
       
        factory(Task::class, 5)->create([
            'user_id' => $user->id
        ]);

        $task = $user->tasks->random();

        $response = $this->actingAs($user)->patch('/api/tasks/' . $task->id, $taskToUpdate);
        $response->assertExactJson(['status' => true, 'updated' => true]);
    }

    public function test_user_can_update_task_status()
    {
        $user = factory(User::class)->create();
        
        factory(Task::class, 5)->create([
            'user_id' => $user->id,
            'completed' => true
        ]);

        $task = $user->tasks->random();

        $response = $this->actingAs($user)->patch('/api/tasks/' . $task->id . '/complete', ['completed' => false]);
        $response->assertExactJson(['status' => true, 'completed' => false]);

        $this->assertFalse(
            !! $task->refresh()->completed
        );
    }

    public function test_user_cannot_update_task_not_belonging_to_him()
    {
        $john = factory(User::class)->create();
        $jane = factory(User::class)->create();

        $taskToUpdate = [
            'name' => 'updatedTask'
        ];
       
        factory(Task::class, 3)->create([
            'user_id' => $john->id
        ]);

        factory(Task::class, 3)->create([
            'user_id' => $jane->id
        ]);

        $response = $this->actingAs($jane)->patch('/api/tasks/' . $john->tasks->random()->id, $taskToUpdate);
        $response->assertForbidden();
    }

    public function test_user_cannot_update_task_which_does_not_exist()
    {
        $user = factory(User::class)->create();

        $taskToUpdate = [
            'name' => 'updatedTask'
        ];

        factory(Task::class, 5)->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->patch('/api/tasks' . 9999999, $taskToUpdate);
        $response->assertNotFound();
    }

    public function test_user_can_delete_task()
    {
        $user = factory(User::class)->create();

        factory(Task::class, 5)->create([
            'user_id' => $user->id
        ]);

        $task = $user->tasks->random();

        $response = $this->actingAs($user)->delete('/api/tasks/' . $task->id);        
        $response
            ->assertOk()
            ->assertExactJson(['status' => true, 'deleted' => true]);
        
        $this->assertEquals($user->refresh()->tasks->count(), 4);
    }

    public function test_user_cannot_delete_task_not_belonging_to_him()
    {
        [$john, $jane] = factory(User::class, 2)->create();

        factory(Task::class)->create([
            'user_id' => $john->id
        ]);

        factory(Task::class)->create([
            'user_id' => $jane->id
        ]);

        $response = $this->actingAs($jane)->delete('/api/tasks/' . $john->tasks()->first()->id);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_task_which_does_not_exist()
    {
        $user = factory(User::class)->create();

        factory(Task::class)->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete('/api/tasks/' . 9999999);
        $response->assertNotFound();
    }
}
