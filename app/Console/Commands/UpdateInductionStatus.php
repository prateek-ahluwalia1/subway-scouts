<?php

namespace App\Console\Commands;

use App\Models\Guard;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateInductionStatus extends Command
{
    protected $signature = 'guard:updateinductionstatus';

    protected $description = 'Reassign inductions for expired guard certificates';

    public function handle()
    {
        $this->reassignExpiredInductions();

        return Command::SUCCESS;
    }

    protected function reassignExpiredInductions()
    {
        $now = Carbon::now();

        $expiredRecords = DB::table('guard_questionnaire_details')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $now)
            ->get();

        if ($expiredRecords->isEmpty()) {
            $this->info('No expired inductions found.');
            return;
        }

        foreach ($expiredRecords as $record) {

            if (!empty($record->certificate_path) && Storage::exists($record->certificate_path)) {
                Storage::delete($record->certificate_path);
            }

            DB::table('guard_questionnaire_details')
                ->where('id', $record->id)
                ->update([
                    'marks' => 0,
                    'certificate_path' => null,
                    'expiry_date' => null,
                    'updated_at' => $now,
                ]);

            DB::table('induction_history')
                ->where('guard_id', $record->guard_id)
                ->where('induction_id', $record->questionnaire_id)
                ->update([
                    'read_status' => 0,
                    'updated_at' => $now,
                ]);

            $guard = Guard::find($record->guard_id);

            if (!empty($guard?->notification_token)) {
                $params = [
                    'message' => $guard->first_name . ' Induction added successfully',
                    'title' => 'Induction Added',
                    'page' => 'roster',
                    'notification_token' => $guard->notification_token,
                ];

                send_push_notification($params);
            }
        }

        $this->info('Expired inductions reassigned successfully.');
    }
}
