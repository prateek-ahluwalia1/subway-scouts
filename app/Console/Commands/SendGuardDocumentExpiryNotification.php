<?php

namespace App\Console\Commands;

use App\Models\GuardDocument;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendGuardDocumentExpiryNotification extends Command
{
    
    protected $signature = 'guard:expiryNotification';

   
    protected $description = "To send notifications to admin when a guard's document expiration is near.";

    public function handle()
    {
        
        return $this->checkGuardDocumentStatus();
    }

    public function checkGuardDocumentStatus()
    {
        $config_dbs = \DB::connection('mysql2')->table('business_data')->get();
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
            

        $documents = [
            'Security License',
            'Passport',
            'Visa',
        ];

        $today = date("Y-m-d"); 
        $next14Days = date("Y-m-d", strtotime($today . "+14 days"));
        $guard_documents = $dynamicDbConnection->table('guards_documents')
            ->where('document_expire', '=', $next14Days)
            ->whereIn('document_name', $documents)
            ->join('guards', 'guards.id', '=', 'guards_documents.guard_id')
            ->select(
                'guards.id',
                'MAX(guards.first_name) as first_name',
                'MAX(guards.last_name) as last_name',
                'MAX(guards.email) as email',
                'guards_documents.guard_id as guard_id',
                'guards_documents.document_name as document_name',
                'guards_documents.document_expire as document_expire'
                )->groupBy('guards.id', 'guards_documents.guard_id', 'guards_documents.document_name', 'guards_documents.document_expire');

        $admins = $dynamicDbConnection->table('users')->where('userType', 'super-admin')->select('email')->get();

        foreach ($admins as $key => $admin) {
            
            $data = [
                'email' => $admin->email,
                'records' => $guard_documents,
            ];
            Mail::send('mail.SendExpDocEmail', $data, function($mail) use ($data){
                $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                $mail->to($data['email'])->subject("Documents going to expire");
            });

        }
        
    }

    \DB::disconnect($newConnection);
}

}
