<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\ToDo;
use App\Models\User;
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
        User::all()->each(fn(User $user) => ToDo::factory(rand(3, 10))
            ->has(
                Tag::factory()->forUser($user)->count(rand(1, 5))
            )->forUser($user)->create());
    }
}
