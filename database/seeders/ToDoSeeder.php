<?php

namespace Database\Seeders;

use App\Models\ToDo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ToDoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::all()->each(fn (User $user) => ToDo::factory(rand(1,20))->forUser($user)->create());
    }
}
