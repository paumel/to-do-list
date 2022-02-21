<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\NotifyAboutExpiredToDos;
use App\Models\ToDo;
use App\Models\User;
use App\Notifications\ExpiredToDosNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyAboutFinishedToDosTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function system_notifies_about_just_finished_todos()
    {
        Notification::fake();
        $user = User::factory()->create();

        $justFinishedToDos = ToDo::factory(2)->forUser($user)->create([
            'due_date' => Carbon::now('Europe/Vilnius')->toDateTimeString(),
            'completed' => false,
        ]);

        $justFinishedToDosButAlreadyCompleted = ToDo::factory(2)->forUser($user)->create([
            'due_date' => Carbon::now('Europe/Vilnius')->toDateTimeString(),
            'completed' => true,
        ]);

        $oldFinishedToDos = ToDo::factory(3)->forUser($user)->create([
            'due_date' => Carbon::now('Europe/Vilnius')->subDays(rand(1, 20))->toDateTimeString(),
        ]);

        $notFinishedToDos = ToDo::factory(3)->forUser($user)->create([
            'due_date' => Carbon::now('Europe/Vilnius')->addDays(rand(1, 20))->toDateTimeString(),
        ]);

        $otherUserJustFinishedToDos = ToDo::factory(2)->forUser(User::factory()->create())->create([
            'due_date' => Carbon::now('Europe/Vilnius')->toDateTimeString(),
            'completed' => false,
        ]);

        $this->artisan(NotifyAboutExpiredToDos::class)->assertExitCode(0);

        Notification::assertSentTo($user, function (ExpiredToDosNotification $notification, $channels) use ($justFinishedToDos) {
            return $notification->toDos->pluck('id')->diff($justFinishedToDos->pluck('id'))->isEmpty();
        });
    }
}
