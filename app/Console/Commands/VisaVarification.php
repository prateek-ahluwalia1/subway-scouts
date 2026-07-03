<?php

namespace App\Console\Commands;

use App\Models\Guard;
use App\Models\VisaDetails;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VisaVarification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check.visa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'visa varificatgion check once in a month ';

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
        // $config_dbs = DB::connection('mysql2')->table('business_data')->where('hide', 1)->where('database_name', 'staffingsolution_scouts_247securitygroup')->get();
        // foreach($config_dbs as $db)
        // {
        //     $connectionConfig['driver'] = 'mysql';
        //     $connectionConfig['host'] = env('DB_HOST');
        //     $connectionConfig['database'] = $db->database_name;
        //     $connectionConfig['username'] = env('DB_USERNAME');
        //     $connectionConfig['password'] = env('DB_PASSWORD');
        //     $newConnection = 'mysql';
        //     config(['database.connections.' . $newConnection => $connectionConfig]);
        //     $dynamicDbConnection = DB::connection($newConnection);
        //     $guards = Guard::where('guard_status', 'active')->where('is_available', 'yes')
        //     ->whereNotNull(['dob', 'country'])
        //     ->whereHas('guardDocuments', function ($query) {
        //         $query->whereIn('document_type', ['passport'])
        //             ->whereNotNull('document_no');
        //     })
        //     ->whereDoesntHave('guardDocuments', function ($query) {
        //         $query->where('document_category', 'permanent_resident');
        //     })
        //     ->whereDoesntHave('guardDocuments', function ($query) {
        //         $query->where('document_category', 'citizen');
        //     })
        //     ->with(['guardDocuments' => function ($query) {
        //         $query->whereIn('document_type', ['passport'])
        //             ->whereNotNull('document_no');
        //     }])
        //     ->where(function ($query) {
        //         $query->whereNull('last_visa_at')
        //             ->orWhereMonth('last_visa_at', '!=', now()->month);
        //     })
        //     ->orderBy('id')
        //     ->take(10)
        //     ->get();
        //     $getPortSetting = $dynamicDbConnection->table('portal_settings')->select('id', 'visa_mail', 'visa_password')->first();
        //     if($getPortSetting->visa_mail && $getPortSetting->visa_password){
        //         foreach($guards as $guard){
        //             if($guard->guardDocuments){
        //                 foreach($guard->guardDocuments as $doc){
        //                     // if($doc->document_type == 'visa'){
        //                     //     $visaNum = $doc->document_no; 
        //                     // }
        //                     if($doc->document_type == 'passport'){
        //                         $ppNum = $doc->document_no; 
        //                     }
        //                 }
        //                 if($ppNum){
        //                     $carbonDate = Carbon::createFromFormat('d-m-Y', $guard['dob']);
        //                     $formattedDOB = $carbonDate->format('d M Y');
        //                     $response = Http::timeout(60)->post('http://62.72.13.17/search', ["family_name"=> $guard->name,"date_of_birth"=> $formattedDOB,"document_number"=> $ppNum,"select_country"=> $guard->country, 'type'=>'new', 'email'=>$getPortSetting->visa_mail, 'password'=>$getPortSetting->visa_password]);
        //                     $jsonResponse = json_decode($response, true);
        //                     if(isset($jsonResponse) && $jsonResponse['status'] == 'success'){
        //                         VisaDetails::where('guard_id', $guard['id'])->delete();
        //                         $dynamicDbConnection->table('visa_details')->insert([
        //                             'guard_id' => $guard['id'],
        //                             'details' => $response,
        //                             'country' => $guard['country'],
        //                             'dob' => $formattedDOB,
        //                             'passport_no' => $ppNum,
        //                             'guard_name' => $guard['name'],
        //                             'is_correct' => 1
        //                         ]);
        //                         if(isset($jsonResponse['results'][13]) && $jsonResponse['results'][13]['label'] == 'Visa expiry date'){
        //                             // $dateTime = new DateTime($jsonResponse['results'][13]['value']);
        //                             $formattedExpiry = $jsonResponse['results'][13]['value'];
        //                             // $dynamicDbConnection->table('guards_documents')->where(['guard_id'=> $guard['id'], 'document_type'=>'visa'])->update([
        //                             //     'document_expire' => $formattedExpiry
        //                             // ]);
        //                         }
        //                         $dynamicDbConnection->table('guards')->where('id', $guard['id'])->update(['last_visa_at'=> now()]);
        //                     }else{
        //                         VisaDetails::where('guard_id', $guard['id'])->delete();
        //                         $dynamicDbConnection->table('visa_details')->insert([
        //                             'guard_id' => $guard['id'],
        //                             'details' => $response,
        //                             'country' => $guard['country'],
        //                             'dob' => $formattedDOB,
        //                             'passport_no' => $ppNum,
        //                             'guard_name' => $guard['name'],
        //                             'is_correct' => 0
        //                         ]);
    
        //                     } 
        //                 }else{
        //                     VisaDetails::where('guard_id', $guard['id'])->delete();
        //                     $dynamicDbConnection->table('visa_details')->insert([
        //                         'guard_id' => $guard['id'],
        //                         'details' => Null,
        //                         'country' => $guard['country'],
        //                         'dob' => $guard['dob'],
        //                         'passport_no' => $ppNum,
        //                         'guard_name' => $guard['name'],
        //                         'is_correct' => 0
        //                     ]);
        //                 }
        //             }
        //         }
        //     }
        //     DB::disconnect($newConnection);
        // }
        // return 0;
    }
}
