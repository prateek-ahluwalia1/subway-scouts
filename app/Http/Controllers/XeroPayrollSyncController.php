<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessXeroPayrollJob;
use App\Models\XeroEmployeeMap;
use App\Models\XeroPayrollSync;
use App\Services\Xero\XeroHttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XeroPayrollSyncController extends Controller
{
    public function __construct(private XeroHttpClient $xero) {}

    // ------------------------------------------------------------------
    // POST /api/xero/sync-payroll
    // ------------------------------------------------------------------
    public function sync(Request $request)
    {
        $request->validate([
            'fortnight_start'    => 'required|date',
            'fortnight_end'      => 'required|date|after:fortnight_start',
            'rosters'            => 'required|array|min:1',
            'rosters.*.guard_id' => 'required|integer',
        ]);

        $start = $request->fortnight_start;
        $end   = $request->fortnight_end;

        // ── Duplicate check ──────────────────────────────────────────
        $existing = XeroPayrollSync::where('fortnight_start', $start)
            ->where('fortnight_end', $end)
            ->first();

        if ($existing && $existing->status === 'success') {
            return response()->json([
                'success' => false,
                'message' => 'This fortnight has already been synced successfully to Xero.',
                'sync'    => $existing,
            ], 422);
        }

        if ($existing && $existing->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Sync is already in progress for this fortnight.',
            ], 422);
        }

        // ── Only sync guards that are already mapped to Xero ─────────
        $guardIds  = collect($request->rosters)->pluck('guard_id')->unique()->values();
        $mappedIds = XeroEmployeeMap::whereIn('crm_employee_id', $guardIds)
                       ->pluck('xero_employee_id', 'crm_employee_id');

        $mappedRosters = collect($request->rosters)
            ->filter(fn($r) => $mappedIds->has($r['guard_id']))
            ->values()
            ->toArray();

        if (empty($mappedRosters)) {
            return response()->json([
                'success' => false,
                'message' => 'No guards in this paysheet are synced to Xero yet. Push at least one guard first.',
            ], 422);
        }

        $skippedCount = $guardIds->count() - $mappedIds->count();

        // ── Create/reset sync log as pending ─────────────────────────
        $syncLog = XeroPayrollSync::updateOrCreate(
            ['fortnight_start' => $start, 'fortnight_end' => $end],
            [
                'status'         => 'pending',
                'xero_payrun_id' => null,
                'error_message'  => null,
                'synced_at'      => null,
                'timesheet_ids'  => null,
            ]
        );

        // ── Dispatch job with full roster data ────────────────────────
        ProcessXeroPayrollJob::dispatch($start, $end, $mappedRosters);

        return response()->json([
            'success' => true,
            'message' => 'Payroll sync started. ' . ($skippedCount > 0 ? "{$skippedCount} unmapped guard(s) skipped." : ''),
            'sync_id' => $syncLog->id,
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/xero/sync-status
    // ------------------------------------------------------------------
    public function status(Request $request)
    {
        $request->validate([
            'fortnight_start' => 'required|date',
            'fortnight_end'   => 'required|date',
        ]);

        $sync = XeroPayrollSync::where('fortnight_start', $request->fortnight_start)
            ->where('fortnight_end', $request->fortnight_end)
            ->first();

        return response()->json([
            'status'         => $sync?->status ?? 'not_synced',
            'xero_payrun_id' => $sync?->xero_payrun_id,
            'synced_at'      => $sync?->synced_at,
            'error_message'  => $sync?->error_message,
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/xero/sync-log
    // ------------------------------------------------------------------
    public function log()
    {
        return response()->json(
            XeroPayrollSync::orderByDesc('created_at')->limit(50)->get()
        );
    }

    // ------------------------------------------------------------------
    // GET /api/xero/pay-runs
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        $query = XeroPayrollSync::orderBy('created_at', 'desc');

        if ($request->has('fortnight_start')) {
            $query->where('fortnight_start', $request->fortnight_start);
        }

        $payRuns = $query->get()->map(function ($run) {
            return [
                'id'              => $run->id,
                'fortnight_start' => $run->fortnight_start,
                'fortnight_end'   => $run->fortnight_end,
                'xero_payrun_id'  => $run->xero_payrun_id,
                'status'          => $run->status,
                'error_message'   => $run->error_message,
                'synced_at'       => $run->synced_at,
                'created_at'      => $run->created_at,
            ];
        });

        return response()->json(['success' => true, 'data' => $payRuns]);
    }

    // ------------------------------------------------------------------
    // DELETE /api/xero/pay-runs/{id}
    // ------------------------------------------------------------------
    public function destroy(int $id)
    {
        $sync = XeroPayrollSync::findOrFail($id);

        // Step 1: Delete timesheets using stored IDs
        $this->deleteTimesheetsForPeriod($sync);

        // Step 2: Delete Xero PayRun if exists
        if (!empty($sync->xero_payrun_id)) {
            try {
                $response = $this->xero->get('/payroll.xro/1.0/PayRuns/' . $sync->xero_payrun_id);
                $status   = $response['PayRuns'][0]['PayRunStatus'] ?? null;

                if ($status === 'DRAFT') {
                    $this->xero->post('/payroll.xro/1.0/PayRuns/' . $sync->xero_payrun_id, [
                        'PayRunID'     => $sync->xero_payrun_id,
                        'PayRunStatus' => 'DELETED',
                    ]);
                    Log::info("[XeroPayRun] Deleted DRAFT PayRun {$sync->xero_payrun_id}");
                } elseif ($status === 'POSTED') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pay run is already posted in Xero. Please revert it to Draft in Xero first, then try again.',
                    ], 422);
                }
            } catch (\Exception $e) {
                Log::warning("[XeroPayRun] Could not delete PayRun {$sync->xero_payrun_id}: " . $e->getMessage());
            }
        }

        // Step 3: Remove from CRM DB
        $sync->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pay run and timesheets deleted. You can now re-sync.',
        ]);
    }

    private function deleteTimesheetsForPeriod(XeroPayrollSync $sync): void
    {
        $timesheetIds = json_decode($sync->timesheet_ids ?? '[]', true);

        if (empty($timesheetIds)) {
            Log::info("[XeroPayRun] No timesheet IDs stored — skipping timesheet deletion.");
            return;
        }

        foreach ($timesheetIds as $tsId) {
            try {
                try {
                    $this->xero->post("/payroll.xro/2.0/Timesheets/{$tsId}/RevertToDraft", []);
                    Log::info("[XeroPayRun] Reverted timesheet {$tsId} to DRAFT");
                } catch (\Exception $e) {
                    Log::info("[XeroPayRun] Revert skipped for {$tsId}: " . $e->getMessage());
                }

                $this->xero->delete("/payroll.xro/2.0/Timesheets/{$tsId}");
                Log::info("[XeroPayRun] ✓ Deleted timesheet {$tsId}");

            } catch (\Exception $e) {
                Log::warning("[XeroPayRun] Could not delete timesheet {$tsId}: " . $e->getMessage());
            }

            sleep(1);
        }
    }
}