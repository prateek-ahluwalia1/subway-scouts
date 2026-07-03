<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
class CrmCustomerStatusCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:customerStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mail to only anz business number of lead status.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $previousDay = Carbon::now()->subDay();
        $config_dbs = \DB::connection('mysql2')->table('business_data')->where('database_name', 'staffingsolution_scouts_anz_globa')->get();
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
            $subject = 'Crm Lead Count!';
            $message = 'You have count of crm leads.</br> Team TheScouts';
            $userCounts = User::leftJoin('crm_customers as won', function($join) use ($previousDay) {
                $join->on('users.id', '=', 'won.won_by')
                     ->whereDate('won.updated_at', '=', $previousDay);
            })
            ->leftJoin('crm_customers as lost', function($join) use ($previousDay) {
                $join->on('users.id', '=', 'lost.loss_by')
                     ->whereDate('lost.updated_at', '=', $previousDay);
            })
            ->leftJoin('crm_customers as contact', function($join) use ($previousDay) {
                $join->on('users.id', '=', 'contact.contacted_by')
                     ->whereDate('contact.updated_at', '=', $previousDay);
            })
            ->leftJoin('crm_customers as created', function($join) use ($previousDay) {
                $join->on('users.id', '=', 'created.created_by')
                     ->whereDate('created.created_at', '=', $previousDay);
            })
            ->select(
                'users.name as user_name',
                'users.logout_at as last_logout',
                'users.last_login as last_login',
                DB::raw('COUNT(DISTINCT won.id) as won_count'),
                DB::raw('COUNT(DISTINCT lost.id) as lost_count'),
                DB::raw('COUNT(DISTINCT contact.id) as contact_count'),
                DB::raw('COUNT(DISTINCT created.id) as created_count')
            )
            ->groupBy('users.id')
            ->get();
            $mainArr = [
                'data' => $userCounts,
                'date' =>  $previousDay->format('d-m-Y'),
            ];
            $mails = ['sahil@anzglobaltravel.com','divay@anzglobaltravel.com', 'moizalig16@gmail.com'];
            if(count($userCounts) > 0){
                foreach($mails as $mail){
                    crmLeadCount($mail, $subject, $message, $mainArr);
                }
            }
            \DB::disconnect($newConnection);
        }
        return Command::SUCCESS;
    }
}
