<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrNew(['email' => 'kate@gmail.com']);
        if (!$user->exists) {
            $user->fill([
                'name' => 'Kate',
                'email' => 'kate@gmail.com',
                'password' => bcrypt('password'),
            ])->save();
        }
        $user = User::firstOrNew(['email' => 'joe@gmail.com']);
        if (!$user->exists) {
            $user->fill([
                'name' => 'Joe',
                'email' => 'joe@gmail.com',
                'password' => bcrypt('password'),
            ])->save();
        }
        $user = User::firstOrNew(['email' => 'ola@gmail.com']);
        if (!$user->exists) {
            $user->fill([
                'name' => 'Ola',
                'email' => 'ola@gmail.com',
                'password' => bcrypt('password'),
            ])->save();
        }

    }
}
