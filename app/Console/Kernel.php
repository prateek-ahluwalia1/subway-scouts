<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('crm:customerStatus')->dailyAt('06:00');
        // $schedule->command('crm.reminders')->dailyAt('06:00');
        // $schedule->command('delete:userActivity')->everyMinute();
        // $schedule->command('guard:hoursNotification')->fridays()->at('18:00');
        // $schedule->command('guard:expiryNotification')->dailyAt('06:00');
        // $schedule->command('company:expiryNotification')->dailyAt('09:00');
        // $schedule->command('check.visa')->dailyAt('06:00');
        // $schedule->command('guard:checkDocuments')->everyMinute();
        // $schedule->command('guard:updateinductionstatus')->dailyAt('09:00');
        // $schedule->command('guard.UpdateGuardsWorkingHrs')->dailyAt('09:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
    protected $commands = [
    ];
}
