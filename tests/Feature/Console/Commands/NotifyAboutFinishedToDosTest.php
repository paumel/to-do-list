<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\NotifyAboutFinishedToDos;
use App\Models\ToDo;
use App\Models\User;
use App\Notifications\FinishedToDosNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyAboutFinishedToDosTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function system_notifies_about_yesterday_finished_todos()
    {
        Notification::fake();
        $user = User::factory()->create();

        $yesterdayFinishedToDos = ToDo::factory(2)->forUser($user)->create([
            'due_date' => Carbon::yesterday()->toDateTimeString(),
            'finished' => false,
        ]);

        $yesterdayFinishedButAlreadyCompleted = ToDo::factory(2)->forUser($user)->create([
            'due_date' => Carbon::yesterday()->toDateTimeString(),
            'finished' => true,
        ]);

        $oldFinishedToDos = ToDo::factory(3)->forUser($user)->create([
            'due_date' => Carbon::yesterday()->subDays(rand(1, 20))->toDateTimeString(),
        ]);

        $notFinishedToDos = ToDo::factory(3)->forUser($user)->create([
            'due_date' => Carbon::today()->addDays(rand(1, 20))->toDateTimeString(),
        ]);

        $otherUserYesterdayFinishedToDos = ToDo::factory(2)->forUser(User::factory()->create())->create([
            'due_date' => Carbon::yesterday()->toDateTimeString(),
            'finished' => false,
        ]);

        $this->artisan(NotifyAboutFinishedToDos::class)->assertExitCode(0);

        Notification::assertSentTo($user, function (FinishedToDosNotification $notification, $channels) use ($yesterdayFinishedToDos) {
            return $notification->toDos->pluck('id')->diff($yesterdayFinishedToDos->pluck('id'))->isEmpty();
        });
    }
}
