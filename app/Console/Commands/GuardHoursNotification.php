<?php

namespace App\Console\Commands;

use App\Models\Guard;
use App\Models\GuardDocument;
use App\Models\User;
use App\Models\JobRoster;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class GuardHoursNotification extends Command
{
    
    protected $signature = 'guard:hoursNotification';

   
    protected $description = "Send notifications to guards.";

    public function handle()
    {
        
        return $this->checkGuardHoursNotification();
    }
    function calculateFutureMonthFourthnight($givenDate)
    {
        $startDate = '2024-06-17';
        $formattedDate = $givenDate->format('Y-m-d');
    
        $startTime = strtotime($startDate);
        $endTime = strtotime($formattedDate);
        $secondsDiff = $endTime - $startTime;
        $daysDiff = floor($secondsDiff / (60 * 60 * 24));
        $totalFourthnight = floor($daysDiff / 14) * 14;
    
        $FourthnightStartDate = date('Y-m-d', strtotime($startDate . ' + ' . $totalFourthnight . ' days'));
        $FourthnightEndDate = date('Y-m-d', strtotime($FourthnightStartDate . ' + 13 days'));
    
        $ret['week_start'] = $FourthnightStartDate;
        $ret['week_end'] = $FourthnightEndDate;
        return $ret;
    }

    function get_current_month_hours_guards($guardId, $start, $end, $shifIsPressed, $event_id = 0 )
    {
        $data = JobRoster::where('guard_id', $guardId)
        ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
        ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)))
        ->where('id', '!=', $event_id)
        ->whereNull('deleted_at')
        ->sum('hours');
        return $data;
    }
    
    public function checkGuardHoursNotification()
    {
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
       
            $admins = $dynamicDbConnection->table('users')->where('userType', 'super-admin')->select('email')->get();
            $week_array = $this->calculateFutureMonthFourthnight(Carbon::today());
            $fullTimerGuards = Guard::where(['guard_status'=> 'active', 'admin_approval_status'=> 'active', 'is_available'=> 'yes','staff_type'=> 'full_time'])
                ->orderBy('first_name')
                ->get();
            foreach ($fullTimerGuards as $key => $guard){
                $currentMonthData = $this->get_current_month_hours_guards($guard->id, $week_array['week_start'], $week_array['week_end'], isset($$fullTimerGuards->shifIsPressed) ? true : false);
                if($currentMonthData < 72){
                    $mainArr[] = $guard;
                }
            }

            if(!empty($mainArr)){
                foreach ($admins as $key => $admin)
                {
                    $data = [
                        'email' => $admin->email,
                        'records' => $mainArr,
                    ];

                    Mail::send('mail.GuardsWorkingHoursEmail', $data, function($mail) use ($data){
                        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                        $mail->to($data['email'])->subject("Guard job Hours");
                    });
                }

            }

      \DB::disconnect($newConnection);
      
       }
    }
}
