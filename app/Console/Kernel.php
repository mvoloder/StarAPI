<?php

namespace App\Console;

use App\Helpers\WorkDays;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Helpers\LastWorkDay;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SprintReminder::class,
        Commands\XpDeduction::class,
        Commands\UnfinishedTasks::class,
        Commands\EmailProfilePerformance::class,
        Commands\MonthlyMinimumCheck::class,
        Commands\TaskPriority::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sprint:remind')
            ->twiceDaily(8, 14);

        $schedule->command('xp:activity:auto-deduct')
            ->dailyAt('13:00');

        $schedule->command('unfinished:tasks:auto-move')
            ->dailyAt('00:01');

        $schedule->command('email:profile:performance 7')
            ->weekly()
            ->mondays()
            ->at('08:00');

        $schedule->command('email:profile:performance 0 --accountants')->when(function () {
            $workDays = WorkDays::getWorkDays();
            $lastWorkDay = end($workDays);
            return Carbon::parse($lastWorkDay)->isToday();
        });

        $schedule->command('employee:minimum:check')
            ->monthlyOn(1, '08:00');

        $schedule->command('slackMessage:send')
            ->everyThirtyMinutes();
    }
}
