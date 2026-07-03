<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guard;
use App\Models\GuardDocument;

class CheckGuardDocumentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guard:checkDocuments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For Check Guard Documents Expires';

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
        $this->checkGuardDocumentStatus();
        // return 0;
        // return Command::SUCCESS;
    }



public function checkGuardDocumentStatus()
{
    // $today = date("Y/m/d");
    // $date = dbFormate($today);
    
    // $guard_documents = GuardDocument::where('document_expire', '<', $date)
    //     ->whereIn('document_type', ['visa', 'passport', 'security_license'])
    //     ->join('guards', 'guards.id', '=', 'guards_documents.guard_id')
    //     ->where('guards.guard_status', 'active')
    //     ->select('guards.id')
    //     ->groupBy('guard_id')
    //     ->get();
    
    //     foreach ($guard_documents as $guard) {
    //         Guard::where('id', $guard->id)->update(['guard_status' => 'document_exp']);
    //         \Log::info("Cron is working fine for Guard ID: " . $guard->id);
    //     }

    $today = date("Y/m/d");
    $today_time = strtotime($today);
    
    $guard = Guard::where('guard_status', 'active')->with('guardDocuments')->get();

foreach ($guard->guardDocuments as $key => $value){
        if($value->document_type == 'visa' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
            Guard::where('id', $guard->id)->update(['guard_status' => 'document_exp']);
            break;
        }elseif($value->document_type == 'passport' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
            Guard::where('id', $guard->id)->update(['guard_status' => 'document_exp']);
            break;
        }elseif($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null)){
            if($value->document_expire != 'current, pending renewal' && $today_time > strtotime($value->document_expire)){
                Guard::where('id', $guard->id)->update(['guard_status' => 'document_exp']);
                break;
            }
        }else{
            Guard::where('id', $guard->id)->update(['guard_status' => 'active']);
        }
    }
}



}
