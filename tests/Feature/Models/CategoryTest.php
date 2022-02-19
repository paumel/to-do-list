<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\ToDo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function category_has_remaining_to_do_count()
    {
        $category = Category::factory()->create(['max_to_dos' => 5]);
        ToDo::factory(3)->forCategory($category)->create();

        $this->assertEquals(2, $category->fresh()->remaining_to_dos_count);
    }
}
