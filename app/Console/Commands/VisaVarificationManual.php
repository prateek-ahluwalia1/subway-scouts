<?php

namespace App\Console\Commands;

use App\Models\Guard;
use App\Models\VisaDetails;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VisaVarificationManual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visaVarification.manual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'manual visa varificatgion check once in a month ';

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
        // $config_dbs = DB::connection('mysql2')->table('business_data')->get();
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
            // $guards = Guard::where('guard_status', 'active')->whereNotNull(['dob', 'country'])
            // ->with(['guardDocuments' => function ($query) {
            //     $query->whereIn('document_type', ['passport', 'visa'])
            //       ->where('document_no', '!=', 'null');
            // }])->get();
            // foreach($guards as $guard){
            //     if($guard->guardDocuments){
            //         foreach($guard->guardDocuments as $doc){
            //             // if($doc->document_type == 'visa'){
            //             //     $visaNum = $doc->document_no; 
            //             // }
            //             if($doc->document_type == 'passport'){
            //                 $ppNum = $doc->document_no; 
            //             }
            //         }
            //         if($ppNum){
            //             $carbonDate = Carbon::createFromFormat('m-d-Y', $guard['dob']);
            //             $formattedDOB = $carbonDate->format('d M Y');
            //             $response = Http::post('http://62.72.13.17/search', ["family_name"=> $guard['first_name'].' '.$guard['last_name'],"date_of_birth"=> $formattedDOB,"document_number"=> $ppNum,"select_country"=> $guard->country, 'type'=>'new']);
            //             $jsonResponse = json_decode($response, true);
            //             if($jsonResponse['status'] == 'success'){
            //                 VisaDetails::where('guard_id', $guard['id'])->delete();
            //                 DB::table('visa_details')->insert([
            //                     'guard_id' => $guard['id'],
            //                     'details' => $response,
            //                     'country' => $guard['country'],
            //                     'guard_name' => $guard['first_name'].' '.$guard['last_name'],
            //                     'is_correct' => 1
            //                 ]);    
            //             }; 
            //         }else{
            //             VisaDetails::where('guard_id', $guard['id'])->delete();
            //             DB::table('visa_details')->insert([
            //                 'guard_id' => $guard['id'],
            //                 'details' => 'missing/unavailable',
            //                 'country' => $guard['country'],
            //                 'guard_name' => $guard['first_name'].' '.$guard['last_name'],
            //                 'is_correct' => 0
            //             ]);
            //         }
            //     }
            // }
        //     DB::disconnect($newConnection);
        // }
    }
}
