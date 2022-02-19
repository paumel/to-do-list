<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\ToDo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ToDoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * index
     */

    /** @test */
    public function not_logged_in_user_cant_see_to_do_list()
    {
        $this->get(route('to-dos.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_see_to_do_list()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->get(route('to-dos.index'))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function verified_user_can_see_to_do_list()
    {
        $user = $this->logIn();

        $this->actingAs($user)->get(route('to-dos.index'))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('ToDos/Index')
                ->has('to_dos')
            );
    }

    /** @test */
    public function user_can_see_only_his_to_dos()
    {
        $user = $this->logIn();

        ToDo::factory(5)->forUser($user)->create();

        ToDo::factory(3)->forUser(User::factory()->create())->create();

        $this->actingAs($user)->get(route('to-dos.index'))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('ToDos/Index')
                ->has('to_dos', 5)
            );
    }

    /**
     * create
     */

    /** @test */
    public function not_logged_in_user_cant_see_to_do_create_view()
    {
        $this->get(route('to-dos.create'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_see_to_do_create_view()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->get(route('to-dos.create'))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function verified_user_can_see_to_do_create_view()
    {
        $user = $this->logIn();

        $this->actingAs($user)->get(route('to-dos.create'))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('ToDos/Create')
                ->has('categories')
            );
    }

    /**
     * store
     */

    /** @test */
    public function not_logged_in_user_cant_create_to_do()
    {
        $this->post(route('to-dos.store'), ToDo::factory()->raw())
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_create_to_do()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw())
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function title_is_required_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['title' => null]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function title_must_be_shorter_than_255_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['title' => Str::random(256)]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function description_is_required_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['description' => null]))
            ->assertSessionHasErrors('description');
    }

    /** @test */
    public function category_must_be_available_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['category_id' => 2]))
            ->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function category_must_belong_to_user_for_to_do_creation()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['category_id' => $category->id]))
            ->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function category_must_have_free_spaces_for_creation()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create(['max_to_dos' => 2]);
        ToDo::factory(2)->forCategory($category)->create();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['category_id' => $category->id]))
            ->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function due_date_can_be_null_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['due_date' => null]))
            ->assertSessionDoesntHaveErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_after_or_equal_today_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw([
            'due_date' => Carbon::yesterday()->toDateTimeString(),
        ]))->assertSessionHasErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_date_for_to_do_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('to-dos.store'), ToDo::factory()->raw(['due_date' => 'not date']))
            ->assertSessionHasErrors('due_date');
    }

    /** @test */
    public function verified_user_can_create_to_do()
    {
        $user = $this->logIn();
        $data = ToDo::factory()->raw();

        $this->actingAs($user)->post(route('to-dos.store'), $data)
            ->assertRedirect(route('to-dos.index'));

        $this->assertDatabaseHas('to_dos', [
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function verified_user_can_create_to_do_with_category()
    {
        $this->withoutExceptionHandling();
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();
        $data = ToDo::factory()->raw(['category_id' => $category->id]);

        $this->actingAs($user)->post(route('to-dos.store'), $data)
            ->assertRedirect(route('to-dos.index'));

        $this->assertDatabaseHas('to_dos', [
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'user_id' => $user->id,
        ]);

        $this->assertEquals($category->id, ToDo::firstWhere('title', $data['title'])->category->id);
    }

    /**
     * edit
     */

    /** @test */
    public function not_logged_in_user_cant_see_to_do_edit_view()
    {
        $toDo = ToDo::factory()->create();

        $this->get(route('to-dos.edit', $toDo))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_see_to_do_edit_view()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->get(route('to-dos.edit', $toDo))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function user_cant_see_not_his_to_do_edit_view()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->get(route('to-dos.edit', $toDo))
            ->assertForbidden();
    }

    /** @test */
    public function verified_user_can_see_to_do_edit_view()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->get(route('to-dos.edit', $toDo))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('ToDos/Edit')
                ->has('toDo', fn(Assert $page) => $page
                    ->where('id', $toDo->id)
                    ->etc()
                )->has('categories')
            );
    }

    /**
     * update
     */

    /** @test */
    public function not_logged_in_user_cant_update_to_do()
    {
        $toDo = ToDo::factory()->create();

        $this->put(route('to-dos.update', $toDo), ToDo::factory()->raw())
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_update_to_do()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw())
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function title_is_required_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['title' => null]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function title_must_be_shorter_than_255_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['title' => Str::random(256)]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function description_is_required_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['description' => null]))
            ->assertSessionHasErrors('description');
    }

    /** @test */
    public function category_must_be_available_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['category_id' => 12]))
            ->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function category_must_belong_to_user_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();
        $category = Category::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['category_id' => $category->id]))
            ->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function category_must_have_free_spaces_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();
        $category = Category::factory()->forUser($user)->create(['max_to_dos' => 2]);
        ToDo::factory(2)->forCategory($category)->create();


        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['category_id' => $category->id]))
            ->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function due_date_can_be_null_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['due_date' => null]))
            ->assertSessionDoesntHaveErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_after_or_equal_today_for_to_do_update_if_previous_date_was_null()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create(['due_date' => null]);

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw([
            'due_date' => Carbon::yesterday()->toDateTimeString(),
        ]))->assertSessionHasErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_after_or_equal_previous_day_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create([
            'due_date' => Carbon::today()->subDays(2)->toDateTimeString()
        ]);

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw([
            'due_date' => Carbon::yesterday()->toDateTimeString(),
        ]))->assertSessionDoesntHaveErrors();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw([
            'due_date' => Carbon::yesterday()->subDays(2)->toDateTimeString(),
        ]))->assertSessionHasErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_date_for_to_do_update()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), ToDo::factory()->raw(['due_date' => 'not date']))
            ->assertSessionHasErrors('due_date');
    }

    /** @test */
    public function verified_user_can_update_to_do()
    {
        $user = $this->logIn();
        $data = ToDo::factory()->raw([
            'title' => 'Updated',
            'description' => 'Updated description',
            'due_date' => Carbon::today()->addDays(2)->toDateTimeString()
        ]);
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), $data)
            ->assertRedirect(route('to-dos.index'));

        $this->assertDatabaseHas('to_dos', [
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function verified_user_can_update_to_do_with_category()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();
        $data = ToDo::factory()->raw([
            'title' => 'Updated',
            'description' => 'Updated description',
            'due_date' => Carbon::today()->addDays(2)->toDateTimeString(),
            'category_id' => $category->id,
        ]);
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.update', $toDo), $data)
            ->assertRedirect(route('to-dos.index'));

        $this->assertDatabaseHas('to_dos', [
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'user_id' => $user->id,
        ]);
        $this->assertEquals($category->id, $toDo->fresh()->category->id);
    }

    /**
     * destroy
     */

    /** @test */
    public function not_logged_in_user_cant_delete_to_do()
    {
        $toDo = ToDo::factory()->create();

        $this->delete(route('to-dos.destroy', $toDo))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_delete_to_do()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->delete(route('to-dos.destroy', $toDo))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function user_cant_delete_not_his_to_do()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->delete(route('to-dos.destroy', $toDo))
            ->assertForbidden();
    }

    /** @test */
    public function verified_user_can_see_delete_to_do()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->delete(route('to-dos.destroy', $toDo))
            ->assertRedirect(route('to-dos.index'));

        $this->assertModelMissing($toDo);
    }
}
