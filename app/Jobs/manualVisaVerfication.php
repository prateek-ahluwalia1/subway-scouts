<?php

namespace App\Jobs;

use App\Models\Guard;
use App\Models\VisaDetails;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class manualVisaVerfication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $guards = Guard::where('guard_status', 'active')->whereNotNull(['dob', 'country'])
        // ->with(['guardDocuments' => function ($query) {
        //     $query->whereIn('document_type', ['passport', 'visa'])
        //         ->where('document_no', '!=', 'null');
        // }])->get();
        // foreach($guards as $guard){
        //     if($guard->guardDocuments){
        //         foreach($guard->guardDocuments as $doc){
        //             if($doc->document_type == 'visa'){
        //                 $visaNum = $doc->document_no; 
        //             }
        //             if($doc->document_type == 'passport'){
        //                 $ppNum = $doc->document_no; 
        //             }
        //         }
        //         if($visaNum && $ppNum){
        //             $carbonDate = Carbon::createFromFormat('m-d-Y', $guard['dob']);
        //             $formattedDOB = $carbonDate->format('d M Y');
        //             $response = Http::post('http://62.72.13.17/search', ["visa_grant_number"=> $visaNum,"date_of_birth"=> $formattedDOB,"document_number"=> $ppNum,"select_country"=> $guard->country]);
        //             $jsonResponse = json_decode($response, true);
        //             if($jsonResponse['status'] == 'success'){
        //                 VisaDetails::where('guard_id', $guard['id'])->first();
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
    }
}
