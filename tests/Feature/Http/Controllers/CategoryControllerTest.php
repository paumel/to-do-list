<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\ToDo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * index
     */

    /** @test */
    public function not_logged_in_user_cant_see_category_list()
    {
        $this->get(route('categories.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_see_category_list()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('categories.index'))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function verified_user_can_see_category_list()
    {
        $user = $this->logIn();

        $this->actingAs($user)->get(route('categories.index'))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Categories/Index')
                ->has('categories')
            );
    }

    /** @test */
    public function verified_user_can_see_category_list_filtered()
    {
        $this->withoutExceptionHandling();
        $user = $this->logIn();
        $category = Category::factory()->create();
        $category->tags()->attach($tag = Tag::factory()->create(['name' => 'tag']));

        $this->actingAs($user)->get(route('categories.index', ['tag_id' => $tag->id]))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Categories/Index')
                ->has('categories')
            );
    }

    /** @test */
    public function user_can_see_only_his_categories()
    {
        $user = $this->logIn();

        Category::factory(5)->forUser($user)->create();

        Category::factory(3)->forUser(User::factory()->create())->create();

        $this->actingAs($user)->get(route('categories.index'))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Categories/Index')
                ->has('categories', 5)
            );
    }

    /**
     * create
     */

    /** @test */
    public function not_logged_in_user_cant_see_category_create_view()
    {
        $this->get(route('categories.create'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_see_category_create_view()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('categories.create'))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function verified_user_can_see_category_create_view()
    {
        $user = $this->logIn();

        $this->actingAs($user)->get(route('categories.create'))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Categories/Create')
            );
    }

    /**
     * store
     */

    /** @test */
    public function not_logged_in_user_cant_create_category()
    {
        $this->post(route('categories.store'), Category::factory()->raw())
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_create_category()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post(route('categories.store'), Category::factory()->raw())
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function title_is_required_for_category_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)->post(route('categories.store'), Category::factory()->raw(['title' => null]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function title_must_be_shorter_than_255_for_category_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)
            ->post(route('categories.store'), Category::factory()->raw(['title' => Str::random(256)]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function max_to_dos_is_required_for_category_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)
            ->post(route('categories.store'), Category::factory()->raw(['max_to_dos' => null]))
            ->assertSessionHasErrors('max_to_dos');
    }

    /** @test */
    public function max_to_dos_must_be_number_for_category_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)
            ->post(route('categories.store'), Category::factory()->raw(['max_to_dos' => 'not number']))
            ->assertSessionHasErrors('max_to_dos');
    }

    /** @test */
    public function max_to_dos_must_be_greater_than_0_for_category_creation()
    {
        $user = $this->logIn();

        $this->actingAs($user)
            ->post(route('categories.store'), Category::factory()->raw(['max_to_dos' => 0]))
            ->assertSessionHasErrors('max_to_dos');
    }


    /** @test */
    public function tags_must_have_name_for_category_creation()
    {
        $user = $this->logIn();
        $data = Category::factory()->raw();
        $data['tags'] = [''];

        $this->actingAs($user)->post(route('categories.store'), $data)
            ->assertSessionHasErrors('tags.0');
    }

    /** @test */
    public function tags_length_must_be_lower_than_255_for_category_creation()
    {
        $user = $this->logIn();
        $data = Category::factory()->raw();
        $data['tags'] = [Str::random(256)];

        $this->actingAs($user)->post(route('categories.store'), $data)
            ->assertSessionHasErrors('tags.0');
    }


    /** @test */
    public function verified_user_can_create_category()
    {
        $user = $this->logIn();
        $data = Category::factory()->raw();

        $this->actingAs($user)->post(route('categories.store'), $data)
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'title' => $data['title'],
            'max_to_dos' => $data['max_to_dos'],
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function verified_user_can_create_category_with_tags()
    {
        $user = $this->logIn();
        $data = Category::factory()->raw();
        $data['tags'] = ['tag1', 'tag2'];

        $this->actingAs($user)->post(route('categories.store'), $data)
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'title' => $data['title'],
            'max_to_dos' => $data['max_to_dos'],
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'tag1']);
        $this->assertDatabaseHas('tags', ['name' => 'tag2']);

        $category = Category::firstWhere('title', $data['title']);
        $this->assertEquals(['tag1', 'tag2'], $category->tags->pluck('name')->toArray());
    }

    /**
     * edit
     */

    /** @test */
    public function not_logged_in_user_cant_see_category_edit_view()
    {
        $category = Category::factory()->create();

        $this->get(route('categories.edit', $category))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_see_category_edit_view()
    {
        $user = User::factory()->unverified()->create();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)->get(route('categories.edit', $category))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function user_cant_see_not_his_category_edit_view()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->get(route('categories.edit', $category))
            ->assertForbidden();
    }

    /** @test */
    public function verified_user_can_see_category_edit_view()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)->get(route('categories.edit', $category))
            ->assertSuccessful()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Categories/Edit')
                ->has('category', fn(Assert $page) => $page
                    ->where('id', $category->id)
                    ->etc()
                )
            );
    }

    /**
     * update
     */

    /** @test */
    public function not_logged_in_user_cant_update_category()
    {
        $category = Category::factory()->create();

        $this->put(route('categories.update', $category), Category::factory()->raw())
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_update_category()
    {
        $user = User::factory()->unverified()->create();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->put(route('categories.update', $category), Category::factory()->raw())
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function title_is_required_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->put(route('categories.update', $category), Category::factory()->raw(['title' => null]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function title_must_be_shorter_than_255_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->put(route('categories.update', $category), Category::factory()->raw([
                'title' => Str::random(256),
            ]))
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function max_to_dos_is_required_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->put(route('categories.update', $category), Category::factory()->raw(['max_to_dos' => null]))
            ->assertSessionHasErrors('max_to_dos');
    }

    /** @test */
    public function max_to_dos_must_be_number_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->put(route('categories.update', $category), Category::factory()->raw([
                'max_to_dos' => 'not number',
            ]))
            ->assertSessionHasErrors('max_to_dos');
    }

    /** @test */
    public function max_to_dos_must_be_greater_than_0_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->put(route('categories.update', $category), Category::factory()->raw(['max_to_dos' => 0]))
            ->assertSessionHasErrors('max_to_dos');
    }

    /** @test */
    public function tags_must_have_value_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();
        $data = Category::factory()->raw();
        $data['tags'] = [''];

        $this->actingAs($user)->put(route('categories.update', $category), $data)
            ->assertSessionHasErrors('tags.0');
    }

    /** @test */
    public function tags_must_lenght_must_be_lower_than_255_for_category_update()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();
        $data = Category::factory()->raw();
        $data['tags'] = [Str::random(256)];

        $this->actingAs($user)->put(route('categories.update', $category), $data)
            ->assertSessionHasErrors('tags.0');
    }

    /** @test */
    public function verified_user_can_update_category()
    {
        $user = $this->logIn();
        $data = Category::factory()->raw([
            'title' => 'Updated',
            'max_to_dos' => 9,
        ]);
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('categories.update', $category), $data)
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'title' => $data['title'],
            'max_to_dos' => $data['max_to_dos'],
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function verified_user_can_update_category_tags()
    {
        $user = $this->logIn();
        $data = Category::factory()->raw([
            'title' => 'Updated',
            'max_to_dos' => 9,
        ]);
        $data['tags'] = ['tag1', 'tag2'];
        $category = Category::factory()->forUser($user)->create();
        $category->tags()->attach(Tag::factory()->create(['name' => 'oldtag1']));

        $this->actingAs($user)->put(route('categories.update', $category), $data)
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'title' => $data['title'],
            'max_to_dos' => $data['max_to_dos'],
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'tag1']);
        $this->assertDatabaseHas('tags', ['name' => 'tag2']);

        $this->assertEquals(['tag1', 'tag2'], $category->fresh()->tags->pluck('name')->toArray());
    }

    /**
     * destroy
     */

    /** @test */
    public function not_logged_in_user_cant_delete_category()
    {
        $category = Category::factory()->create();

        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_delete_category()
    {
        $user = User::factory()->unverified()->create();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function user_cant_delete_not_his_category()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->delete(route('categories.destroy', $category))
            ->assertForbidden();
    }

    /** @test */
    public function verified_user_can_see_delete_category()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertModelMissing($category);
    }

    /** @test */
    public function category_deletion_unsets_category_from_dependant_to_dos()
    {
        $user = $this->logIn();
        $category = Category::factory()->forUser($user)->create();
        $toDo = ToDo::factory()->create(['category_id' => $category->id]);

        $this->actingAs($user)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertModelMissing($category);
        $this->assertNull($toDo->fresh()->category_id);
    }
}
