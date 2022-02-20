<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\ToDo;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ToDo::all()->each(function (ToDo $toDo) {
            if (rand(0, 1)) {
                $category = Category::factory()->has(
                    Tag::factory()->forUser($toDo->user)->count(rand(1, 5))
                )->forUser($toDo->user)->create();

                $toDo->category()->associate($category);
                $toDo->save();
            }
        });
    }
}
