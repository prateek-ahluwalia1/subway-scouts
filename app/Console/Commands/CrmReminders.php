<?php

namespace App\Console\Commands;

use App\Models\CrmReminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CrmReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm.reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to all admin according to reminders table.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');
        $config_dbs = \DB::connection('mysql2')->table('business_data')->where('hide', 1)->get();
        foreach($config_dbs as $db)
        {
            $connectionConfig['driver'] = 'mysql';
            $connectionConfig['host'] = env('DB_HOST');
            $connectionConfig['database'] = $db->database_name;
            $connectionConfig['username'] = env('DB_USERNAME');
            $connectionConfig['password'] = env('DB_PASSWORD');
            $newConnection = 'mysql';
            config(['database.connections.' . $newConnection => $connectionConfig]);
            $dynamicDbConnection = \DB::connection($newConnection);
            $getReminders = CrmReminder::whereDate('date', $today)->with(['createdBy'])->where('is_sent', 0)->get();
            $subject = 'AMG Security Reminder!';
            $message = 'You have some reminders today.</br> Team TheScouts';
            if($getReminders){
                foreach($getReminders as $reminder){
                    if($reminder['is_sent'] == 0){
                        crmReminderMail($reminder['createdBy']->email, $subject, $message, $reminder);
                        $notifyOthers = json_decode($reminder['notify_too']);
                        // return $notifyOthers;
                        if($notifyOthers != null){
                            foreach($notifyOthers as $notiyOther){
                                $getAdmin = User::where('id', $notiyOther)->select('id', 'email')->first();
                                if($getAdmin){
                                    crmReminderMail($getAdmin->email, $subject, $message, $reminder);
                                }
                            }
                        }
                        $dynamicDbConnection->table('crm_reminders')->where('id', $reminder['id'])->update(['is_sent' => 1]);
                    }
                }
            }
            \DB::disconnect($newConnection);
        }
        return Command::SUCCESS;
    }
}
