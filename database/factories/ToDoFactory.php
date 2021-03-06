<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ToDo>
 */
class ToDoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'completed' => $this->faker->boolean(),
            'due_date' => $this->faker->dateTimeBetween('now', '+5 months', 'Europe/Vilnius')->format('Y-m-d H:m:s'),
            'user_id' => fn() => User::factory()->create(),
            'category_id' => null,
        ];
    }

    public function forUser(User $user): ToDoFactory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function forCategory(Category $category): ToDoFactory
    {
        return $this->state(function (array $attributes) use ($category) {
            return [
                'category_id' => $category->id,
            ];
        });
    }
}
