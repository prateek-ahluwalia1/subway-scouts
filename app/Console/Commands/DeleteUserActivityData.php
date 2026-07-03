<?php

namespace App\Console\Commands;

use App\Models\Logging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteUserActivityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:userActivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all previous 15 days data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->deletePreviousLoggingData();
    }
    public function deletePreviousLoggingData(){
        $thresholdDate = Carbon::now()->subDays(15)->toDateString();
        Logging::whereDate('created_at', '<', $thresholdDate)->delete();
        // Log::info($thresholdDate);
    }
}
