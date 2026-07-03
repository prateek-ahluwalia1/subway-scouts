<?php

namespace App\Jobs;

use App\Models\XeroPayrollSync;
use App\Models\XeroTimesheetQueue;
use App\Services\Xero\XeroHttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessTimesheetChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180; // 2 min per chunk of 10 is plenty

    public function __construct(
        private int $syncId,
        private int $chunkIndex
    ) {}

    public function handle(XeroHttpClient $xero): void
    {
        $syncLog = XeroPayrollSync::findOrFail($this->syncId);
        $chunk   = XeroTimesheetQueue::where('sync_id', $this->syncId)
                     ->where('chunk_index', $this->chunkIndex)
                     ->firstOrFail();

        $chunk->update(['status' => 'processing']);

        try {
            $timesheetIds = $this->processChunk($xero, $chunk->employees, $syncLog);

            // Append new IDs to existing stored IDs
            $existing = json_decode($syncLog->timesheet_ids ?? '[]', true);
            $syncLog->update([
                'timesheet_ids' => json_encode(array_merge($existing, $timesheetIds))
            ]);

            $chunk->update(['status' => 'done']);

            Log::info("[XeroPayroll] Chunk {$this->chunkIndex} done. {$this->syncId} — " . count($timesheetIds) . " timesheets.");

            // Check if all chunks are done
            $totalChunks   = XeroTimesheetQueue::where('sync_id', $this->syncId)->count();
            $doneChunks    = XeroTimesheetQueue::where('sync_id', $this->syncId)->where('status', 'done')->count();
            $failedChunks  = XeroTimesheetQueue::where('sync_id', $this->syncId)->where('status', 'failed')->count();

            if ($doneChunks + $failedChunks === $totalChunks) {
                // All chunks finished
                if ($failedChunks === 0) {
                    $syncLog->update([
                        'status'    => 'success',
                        'synced_at' => now(),
                        'error_message' => null,
                    ]);
                    Log::info("[XeroPayroll] ✓ All chunks complete. Sync {$this->syncId} succeeded.");
                } else {
                    $syncLog->update([
                        'status'        => 'failed',
                        'error_message' => "{$failedChunks} chunk(s) failed. Check xero_timesheet_queue table.",
                    ]);
                }
            } else {
                // Dispatch next chunk with 10s delay to respect rate limits
                $nextIndex = $this->chunkIndex + 1;
                $nextChunk = XeroTimesheetQueue::where('sync_id', $this->syncId)
                               ->where('chunk_index', $nextIndex)
                               ->where('status', 'pending')
                               ->first();

                if ($nextChunk) {
                    ProcessTimesheetChunkJob::dispatch($this->syncId, $nextIndex)
                        ->delay(now()->addSeconds(10));
                    Log::info("[XeroPayroll] Dispatched next chunk {$nextIndex} with 10s delay.");
                }
            }

        } catch (\Exception $e) {
            $chunk->update(['status' => 'failed', 'error' => $e->getMessage()]);
            Log::error("[XeroPayroll] Chunk {$this->chunkIndex} failed: " . $e->getMessage());

            // Still check if all chunks are settled
            $totalChunks  = XeroTimesheetQueue::where('sync_id', $this->syncId)->count();
            $doneChunks   = XeroTimesheetQueue::where('sync_id', $this->syncId)->where('status', 'done')->count();
            $failedChunks = XeroTimesheetQueue::where('sync_id', $this->syncId)->where('status', 'failed')->count();

            if ($doneChunks + $failedChunks === $totalChunks) {
                $syncLog->update([
                    'status'        => 'failed',
                    'error_message' => "{$failedChunks} chunk(s) failed. Last error: " . $e->getMessage(),
                ]);
            }

            throw $e; // let queue mark job as failed for retry
        }
    }

    private function processChunk(XeroHttpClient $xero, array $employees, XeroPayrollSync $syncLog): array
    {
        $fortnightStart = substr($syncLog->fortnight_start, 0, 10);
        $fortnightEnd   = substr($syncLog->fortnight_end,   0, 10);
        $start          = Carbon::parse($fortnightStart);
        $end            = Carbon::parse($fortnightEnd);
        $totalDays      = $start->diffInDays($end) + 1;
        $timesheetIds   = [];

        foreach ($employees as $employee) {
            $hasOrdinaryRate = collect($employee['entries'])
                ->first(fn($e) => !empty($e['ordinary_earnings_rate_id']));

            if (!$hasOrdinaryRate) {
                Log::warning("[XeroPayroll] Skipping {$employee['name']} — no ordinary earnings rate.");
                continue;
            }

            // Build timesheet lines map
            $timesheetLinesMap = [];

            foreach ($employee['entries'] as $guard) {
                foreach ($guard['daily'] ?? [] as $dateStr => $levels) {
                    $dayIndex = $start->diffInDays(Carbon::parse($dateStr), false);
                    if ($dayIndex < 0 || $dayIndex >= $totalDays) continue;

                    foreach ($levels as $lvl => $rates) {
                        foreach ($rates as $rateKey => $hours) {
                            if ($hours <= 0) continue;

                            $configKey = "{$rateKey}_l{$lvl}";
                            $rateId    = config("services.xero.{$configKey}");
                            if (empty($rateId)) continue;

                            if (!isset($timesheetLinesMap[$configKey])) {
                                $timesheetLinesMap[$configKey] = [
                                    'earningsRateID' => $rateId,        // ← v2 uses camelCase
                                    'numberOfUnits'  => array_fill(0, $totalDays, 0.0),
                                ];
                            }

                            $timesheetLinesMap[$configKey]['numberOfUnits'][$dayIndex] = round(
                                $timesheetLinesMap[$configKey]['numberOfUnits'][$dayIndex] + $hours, 2
                            );
                        }
                    }
                }
            }

            $timesheetLines = array_values($timesheetLinesMap);

            if (empty($timesheetLines)) {
                Log::warning("[XeroPayroll] No hours for {$employee['name']} — skipped.");
                continue;
            }

            // ── Delete existing timesheet via v2 DELETE ──────────────────
            try {
                $existing = $xero->get('/payroll.xro/2.0/timesheets', [
                    'employeeId' => $employee['xero_employee_id'],
                    'startDate'  => $fortnightStart,
                    'endDate'    => $fortnightEnd,
                ]);
                Log::info("[XeroPayroll] existing timesheets for {$employee['name']}: " . json_encode($existing));

                foreach ($existing['timesheets'] ?? [] as $ts) {
                    $tsId     = $ts['timesheetID'] ?? null;
                    $tsStatus = $ts['status']      ?? '';

                    if (!$tsId) continue;

                    if (in_array($tsStatus, ['APPROVED', 'PROCESSED'])) {
                        // Must revert to draft before deleting — v2 endpoint uses PascalCase action
                        try {
                            $xero->post("/payroll.xro/2.0/timesheets/{$tsId}/RevertToDraft", []);
                            Log::info("[XeroPayroll] Reverted timesheet {$tsId} to DRAFT");
                        } catch (\Exception $revertEx) {
                            Log::warning("[XeroPayroll] Revert skipped for {$tsId}: " . $revertEx->getMessage());
                        }
                    }

                    $xero->delete("/payroll.xro/2.0/timesheets/{$tsId}");
                    Log::info("[XeroPayroll] Deleted existing timesheet {$tsId} for {$employee['name']}");
                }
            } catch (\Exception $e) {
                Log::warning("[XeroPayroll] Could not clear existing timesheet for {$employee['name']}: " . $e->getMessage());
            }

            // ── Create timesheet via v2 POST ─────────────────────────────
            // NOTE: v2 does NOT accept a `status` field on creation.
            // Timesheets are always created as DRAFT by Xero automatically.
            $response = $xero->post('/payroll.xro/2.0/timesheets', [
                'employeeID'     => $employee['xero_employee_id'],
                'startDate'      => $fortnightStart,
                'endDate'        => $fortnightEnd,
                'timesheetLines' => $timesheetLines,
            ]);

            // v2 returns single object not array
            $timesheetId = $response['timesheetID'] ?? null;

            if (!$timesheetId) {
                Log::error("[XeroPayroll] No timesheetID returned for {$employee['name']}. Response: " . json_encode($response));
                continue; // skip, don't throw — let other employees proceed
            }

            Log::info("[XeroPayroll] ✓ Created DRAFT timesheet {$timesheetId} for {$employee['name']} with " . count($timesheetLines) . " line(s).");

            $timesheetIds[] = $timesheetId;
        }

        return $timesheetIds;
    }
}