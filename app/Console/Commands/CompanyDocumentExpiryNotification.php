<?php

namespace App\Console\Commands;

use App\Models\GuardDocument;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CompanyDocumentExpiryNotification extends Command
{
    
    protected $signature = 'company:expiryNotification';

   
    protected $description = "To send notifications to admin when a company document expiration is near.";

    public function handle()
    {
        
        return $this->checkCompanyDocumentStatus();
    }

    public function checkCompanyDocumentStatus()
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
       
            $files = $dynamicDbConnection->table('business_files')
            ->join('users', 'users.id', '=', 'business_files.created_by')
            ->select('business_files.*', 'users.name as created_by', 'users.email')
            ->get();

            foreach($files as $file)
            {
                if(!empty($file->expiry_date))
                {
                    $current_date = Carbon::now()->format('d-m-Y');
                    $expiry_date = Carbon::parse($file->expiry_date);
                    $first_email = $expiry_date->copy()->subDays(30)->format('d-m-Y');
                    $second_email = $expiry_date->copy()->subDays(15)->format('d-m-Y');
                    if($current_date == $first_email)
                    {
                        $data = [
                            'email' => $file->email,
                            'records' => $file,
                        ];
                        Mail::send('mail.SendComExpDocEmail', $data, function($mail) use ($data){
                            $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                            $mail->to($data['email'])->subject("Company Document going to expire");
                        });

                    }
                    elseif($current_date == $second_email)
                    {
                        $data = [
                            'email' => $file->email,
                            'records' => $file,
                        ];
                        Mail::send('mail.SendComExpDocEmail', $data, function($mail) use ($data){
                            $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                            $mail->to($data['email'])->subject("Company Document going to expire");
                        });

                    }
                }

            }

      \DB::disconnect($newConnection);
      
       }

}

}
