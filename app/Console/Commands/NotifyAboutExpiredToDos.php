<?php

namespace App\Console\Commands;

use App\Models\ToDo;
use App\Notifications\ExpiredToDosNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyAboutExpiredToDos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'to-dos:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command notifies about expired to dos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ToDo::with(['user'])
            ->expiredOnDate(Carbon::now())
            ->get()
            ->groupBy('user_id')
            ->each(function ($toDos) {
                $toDos->first()->user->notify(new ExpiredToDosNotification($toDos));
            });

        return 0;
    }
}
