<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ToDo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleToDoFinishedControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function not_logged_in_user_cant_toggle_to_do_finished_field()
    {
        $toDo = ToDo::factory()->create();

        $this->put(route('to-dos.toggle', $toDo))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function not_verified_user_cant_toggle_to_do_finished_field()
    {
        $user = User::factory()->unverified()->create();
        $toDo = ToDo::factory()->forUser($user)->create();

        $this->actingAs($user)->put(route('to-dos.toggle', $toDo))
            ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function user_cant_toggle_not_his_to_do_finished_field()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)->put(route('to-dos.toggle', $toDo))
            ->assertForbidden();
    }

    /** @test */
    public function verified_user_can_toggle_to_do_finished_field()
    {
        $user = $this->logIn();
        $toDo = ToDo::factory()->forUser($user)->create(['finished' => false]);

        $this->actingAs($user)->put(route('to-dos.toggle', $toDo))
            ->assertRedirect();

        $this->assertEquals(true, $toDo->fresh()->finished);

        $this->actingAs($user)->put(route('to-dos.toggle', $toDo))
            ->assertRedirect();

        $this->assertEquals(false, $toDo->fresh()->finished);
    }
}
