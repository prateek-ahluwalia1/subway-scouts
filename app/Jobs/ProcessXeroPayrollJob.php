<?php

namespace App\Jobs;

use App\Models\XeroEmployeeMap;
use App\Models\XeroPayrollSync;
use App\Services\Xero\XeroHttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessXeroPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 900;

    // ── API tracking ─────────────────────────────────────────────────
    private int    $apiCallCount   = 0;
    private int    $dailyRemaining = 9999;
    private int    $minRemaining   = 60;

    public function __construct(
        private string $fortnightStart,
        private string $fortnightEnd,
        private array  $rosters
    ) {}

    // ═══════════════════════════════════════════════════════════════
    // Entry point
    // ═══════════════════════════════════════════════════════════════
    public function handle(XeroHttpClient $xero): void
    {
        $syncLog = XeroPayrollSync::where('fortnight_start', $this->fortnightStart)
            ->where('fortnight_end', $this->fortnightEnd)
            ->firstOrFail();

        try {
            $this->run($xero, $syncLog);

            $syncLog->update([
                'status'        => 'success',
                'error_message' => null,
                'synced_at'     => now(),
            ]);

            Log::info("[XeroPayroll] ✓ Sync complete. Total API calls: {$this->apiCallCount} | Day remaining: {$this->dailyRemaining}");

        } catch (\Exception $e) {
            $syncLog->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("[XeroPayroll] Sync failed after {$this->apiCallCount} API calls: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        XeroPayrollSync::where('fortnight_start', $this->fortnightStart)
            ->where('fortnight_end', $this->fortnightEnd)
            ->update([
                'status'        => 'failed',
                'error_message' => 'Job failed: ' . $e->getMessage(),
            ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // Main flow
    // ═══════════════════════════════════════════════════════════════
    private function run(XeroHttpClient $xero, XeroPayrollSync $syncLog): void
    {
        $byGuard = $this->groupAndSumByGuard($this->rosters);

        if (empty($byGuard)) {
            throw new \RuntimeException('No mapped employees with hours found');
        }

        // Group by xero_employee_id
        $byEmployee = [];
        foreach ($byGuard as $guard) {
            $empId = $guard['xero_employee_id'];
            if (!isset($byEmployee[$empId])) {
                $byEmployee[$empId] = [
                    'xero_employee_id' => $empId,
                    'name'             => $guard['name'],
                    'entries'          => [],
                ];
            }
            $byEmployee[$empId]['entries'][] = $guard;
        }

        $total        = count($byEmployee);
        $timesheetIds = json_decode($syncLog->timesheet_ids ?? '[]', true);
        $isRerun      = !empty($timesheetIds);
        $processed    = 0;
        $created      = 0;
        $skipped      = 0;
        $failed       = 0;

        // ── Check daily limit before starting ────────────────────────
        try {
            $res = $xero->getRawResponse('/payroll.xro/1.0/Employees', ['page' => 1]);
            $this->apiCallCount++;
            $this->dailyRemaining = (int) ($res->header('X-DayLimit-Remaining') ?? 9999);
            $this->minRemaining   = (int) ($res->header('X-MinLimit-Remaining') ?? 60);

            Log::info("[XeroPayroll] Starting sync: {$total} employees | Is re-run: " . ($isRerun ? 'yes' : 'no') . " | Day remaining: {$this->dailyRemaining} | Min remaining: {$this->minRemaining}");

            if ($this->dailyRemaining <= 0) {
                $retryAfter = $res->header('Retry-After') ?? 'unknown';
                throw new \RuntimeException("Xero daily API limit exhausted. Retry after {$retryAfter} seconds.");
            }

            // Estimate calls needed and warn if not enough
            $estimatedCalls = $isRerun ? ($total * 3) : $total;
            if ($this->dailyRemaining < $estimatedCalls) {
                Log::warning("[XeroPayroll] Only {$this->dailyRemaining} calls remaining but need ~{$estimatedCalls}. Will stop if limit reached.");
            }

        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning("[XeroPayroll] Could not check rate limits: " . $e->getMessage() . " — proceeding anyway.");
        }

        // ── Process each employee ────────────────────────────────────
        foreach ($byEmployee as $employee) {
            $processed++;

            // Stop if daily limit critical
            if ($this->dailyRemaining <= 5) {
                $msg = "Daily API limit critical ({$this->dailyRemaining} remaining) after {$this->apiCallCount} calls. Stopped at {$processed}/{$total}.";
                Log::warning("[XeroPayroll] {$msg}");
                $syncLog->update([
                    'timesheet_ids' => json_encode($timesheetIds),
                    'error_message' => $msg,
                ]);
                throw new \RuntimeException($msg);
            }

            // Pause if per-minute limit low
            if ($this->minRemaining <= 3) {
                Log::warning("[XeroPayroll] Minute limit low ({$this->minRemaining}). Pausing 61s before {$employee['name']}...");
                sleep(61);
            }

            Log::info("[XeroPayroll] [{$processed}/{$total}] {$employee['name']} | Calls: {$this->apiCallCount} | Day left: {$this->dailyRemaining}");

            $result = $this->createTimesheet($xero, $employee, $isRerun);

            if ($result === 'skipped') {
                $skipped++;
            } elseif ($result === null) {
                $failed++;
            } else {
                $timesheetIds[] = $result;
                $created++;
            }

            // Save progress after each employee
            $syncLog->update(['timesheet_ids' => json_encode($timesheetIds)]);

            // Small pause between employees
            sleep(1);
        }

        Log::info("[XeroPayroll] ✓ Complete — Created: {$created} | Skipped: {$skipped} | Failed: {$failed} | Total API calls: {$this->apiCallCount} | Day remaining: {$this->dailyRemaining}");
    }

    // ═══════════════════════════════════════════════════════════════
    // Create one timesheet per employee via v1 API
    // ═══════════════════════════════════════════════════════════════
    private function createTimesheet(XeroHttpClient $xero, array $employee, bool $isRerun): ?string
    {
        $start     = Carbon::parse($this->fortnightStart);
        $end       = Carbon::parse($this->fortnightEnd);
        $totalDays = $start->diffInDays($end) + 1;

        // Skip if no ordinary earnings rate
        $hasOrdinaryRate = collect($employee['entries'])
            ->first(fn($e) => !empty($e['ordinary_earnings_rate_id']));

        if (!$hasOrdinaryRate) {
            Log::warning("[XeroPayroll] Skipping {$employee['name']} — no ordinary earnings rate.");
            return 'skipped';
        }

        // Build timesheet lines
        $timesheetLinesMap = [];

        foreach ($employee['entries'] as $guard) {
            foreach ($guard['daily'] ?? [] as $dateStr => $levels) {
                $dayIndex = $start->diffInDays(Carbon::parse($dateStr), false);

                if ($dayIndex < 0 || $dayIndex >= $totalDays) {
                    Log::warning("[XeroPayroll] Shift date {$dateStr} for {$employee['name']} outside fortnight — skipped.");
                    continue;
                }

                foreach ($levels as $lvl => $rates) {
                    foreach ($rates as $rateKey => $hours) {
                        if ($hours <= 0) continue;

                        $configKey = "{$rateKey}_{$lvl}";
                        $rateId    = config("services.xero.{$configKey}");

                        if (empty($rateId)) {
                            Log::warning("[XeroPayroll] No EarningsRateID for '{$configKey}' ({$employee['name']}) — skipped {$hours}h on {$dateStr}.");
                            continue;
                        }

                        if (!isset($timesheetLinesMap[$configKey])) {
                            $timesheetLinesMap[$configKey] = [
                                'EarningsRateID' => $rateId,
                                'NumberOfUnits'  => array_fill(0, $totalDays, 0.0),
                            ];
                        }

                        $timesheetLinesMap[$configKey]['NumberOfUnits'][$dayIndex] = round(
                            $timesheetLinesMap[$configKey]['NumberOfUnits'][$dayIndex] + $hours, 2
                        );
                    }
                }
            }
        }

        $timesheetLines = array_values($timesheetLinesMap);

        if (empty($timesheetLines)) {
            Log::warning("[XeroPayroll] No hours for {$employee['name']} — skipped.");
            return 'skipped';
        }

        // On re-run only: delete existing timesheet first
        if ($isRerun) {
            $this->deleteDraftTimesheetIfExists($xero, $employee['xero_employee_id']);
        }

        // POST timesheet — capture rate limit headers from response
        $result = $this->safePost($xero, '/payroll.xro/1.0/Timesheets', [[
            'EmployeeID'     => $employee['xero_employee_id'],
            'StartDate'      => $start->format('Y-m-d'),
            'EndDate'        => $end->format('Y-m-d'),
            'Status'         => 'DRAFT',
            'TimesheetLines' => $timesheetLines,
        ]], $employee['name']);

        if ($result === null) {
            return null;
        }

        $timesheetId = $result['Timesheets'][0]['TimesheetID'] ?? null;

        if (!$timesheetId) {
            Log::error("[XeroPayroll] No TimesheetID returned for {$employee['name']}. Response: " . json_encode($result));
            return null;
        }

        Log::info("[XeroPayroll] ✓ Created timesheet {$timesheetId} for {$employee['name']} with " . count($timesheetLines) . " line(s).");

        return $timesheetId;
    }

    // ═══════════════════════════════════════════════════════════════
    // Safe POST — no retry, handles each error type gracefully
    // Returns null to skip employee, throws only on daily limit
    // ═══════════════════════════════════════════════════════════════
    private function safePost(XeroHttpClient $xero, string $path, array $payload, string $context = ''): ?array
    {
        try {
            $result = $xero->postWithHeaders($path, $payload);
            $this->apiCallCount++;

            // Update rate limits from response headers — no extra API call
            $this->dailyRemaining = $result['day_remaining'];
            $this->minRemaining   = $result['min_remaining'];

            Log::debug("[XeroPayroll] Call #{$this->apiCallCount} | Day: {$this->dailyRemaining} | Min: {$this->minRemaining}");

            return $result['data'];

        } catch (\RuntimeException $e) {
            $this->apiCallCount++;
            $msg = $e->getMessage();

            // Daily limit exhausted — stop the entire job
            if (str_contains($msg, 'HTTP 429') && str_contains(strtolower($msg), 'day')) {
                Log::error("[XeroPayroll] Daily limit hit. Stopping job.");
                throw new \RuntimeException("Daily API limit hit during sync. Calls made: {$this->apiCallCount}.");
            }

            // Minute limit hit — pause then continue (no retry of same call)
            if (str_contains($msg, 'HTTP 429')) {
                Log::warning("[XeroPayroll] Minute limit hit for {$context}. Pausing 61s then skipping this employee.");
                sleep(61);
                $this->minRemaining = 60; // reset estimate
                return null; // skip this employee, don't retry
            }

            // Xero server error — skip employee, continue
            if (str_contains($msg, 'HTTP 500')) {
                Log::warning("[XeroPayroll] Xero 500 error for {$context} — skipping. Error: {$msg}");
                return null;
            }

            // Validation error — skip employee, continue
            if (str_contains($msg, 'HTTP 400')) {
                Log::warning("[XeroPayroll] Validation error for {$context} — skipping. Error: {$msg}");
                return null;
            }

            // Timeout / connection error — skip employee, continue
            if (
                str_contains($msg, 'timeout') ||
                str_contains($msg, 'cURL') ||
                str_contains($msg, 'Connection') ||
                str_contains($msg, 'SSL')
            ) {
                Log::warning("[XeroPayroll] Connection error for {$context} — skipping. Error: {$msg}");
                return null;
            }

            // Any other error — skip employee, log, continue
            Log::warning("[XeroPayroll] Unexpected error for {$context} — skipping. Error: {$msg}");
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // Safe GET — no retry, returns null on any error
    // ═══════════════════════════════════════════════════════════════
    private function safeGet(XeroHttpClient $xero, string $path, array $query = [], string $context = ''): ?array
    {
        try {
            $response = $xero->get($path, $query);
            $this->apiCallCount++;
            return $response;
        } catch (\RuntimeException $e) {
            $this->apiCallCount++;
            $msg = $e->getMessage();

            if (str_contains($msg, 'HTTP 429') && str_contains(strtolower($msg), 'day')) {
                Log::error("[XeroPayroll] Daily limit hit on GET. Stopping job.");
                throw new \RuntimeException("Daily API limit hit during GET. Calls made: {$this->apiCallCount}.");
            }

            Log::warning("[XeroPayroll] GET error for {$context} — skipping. Error: {$msg}");
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // Delete existing DRAFT/APPROVED timesheet (re-run only)
    // ═══════════════════════════════════════════════════════════════
    private function deleteDraftTimesheetIfExists(XeroHttpClient $xero, string $employeeId): void
    {
        $response = $this->safeGet($xero, '/payroll.xro/1.0/Timesheets', [
            'EmployeeID' => $employeeId,
            'StartDate'  => substr($this->fortnightStart, 0, 10),
            'EndDate'    => substr($this->fortnightEnd,   0, 10),
        ], $employeeId);

        if (!$response) return;

        foreach ($response['Timesheets'] ?? [] as $ts) {
            if (!in_array($ts['Status'] ?? '', ['DRAFT', 'APPROVED'])) continue;

            $this->safePost($xero, "/payroll.xro/1.0/Timesheets/{$ts['TimesheetID']}", [[
                'TimesheetID' => $ts['TimesheetID'],
                'Status'      => 'DELETED',
            ]], "delete-{$ts['TimesheetID']}");

            Log::info("[XeroPayroll] Deleted existing timesheet {$ts['TimesheetID']} (was {$ts['Status']})");
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // Group roster rows by guard+level, summing hours per day
    // ═══════════════════════════════════════════════════════════════
    private function groupAndSumByGuard(array $rosters): array
    {
        $byGuard = [];

        foreach ($rosters as $row) {
            $guardId   = $row['guard_id'];
            $levelNum = preg_replace('/[^0-9]/', '', $row['level'] ?? '1') ?: '1';
            $siteName = strtolower($row['site_name'] ?? '');

            // Detect site type prefix from site name
            $prefix = 'l'; // default — normal level
            if (str_contains($siteName, 'dhhs') || str_contains($siteName, 'treasury') || str_contains($siteName, 'treasure')) {
                $level = 'dhhs_l' . $levelNum;    // → dhhs_l3
            } elseif (str_contains($siteName, 'airport')) {
                $level = 'airport_l' . $levelNum; // → airport_l1
            } else {
                $level = 'l' . $levelNum;         // → l1, l2, l3
            }

            $xeroMap = XeroEmployeeMap::where('crm_employee_id', $guardId)->first();

            if (!$xeroMap) {
                Log::warning("[XeroPayroll] guard_id {$guardId} has no Xero mapping — skipped.");
                continue;
            }

            // Key includes site type so same guard on different sites gets separate timesheets
            $key = "{$guardId}_{$level}";

            if (!isset($byGuard[$key])) {
                $byGuard[$key] = [
                    'guard_id'                  => $guardId,
                    'xero_employee_id'          => $xeroMap->xero_employee_id,
                    'ordinary_earnings_rate_id' => $xeroMap->ordinary_earnings_rate_id,
                    'name'                      => trim(($row['guard_first_name'] ?? '') . ' ' . ($row['guard_last_name'] ?? '')),
                    'level'                     => $level, // e.g. l3, d3, a1
                    'site_type'                 => $prefix,
                    'morning_hours'             => 0.0,
                    'night_hours'               => 0.0,
                    'saturday_morning_hours'    => 0.0,
                    'saturday_night_hours'      => 0.0,
                    'sunday_morning_hours'      => 0.0,
                    'sunday_night_hours'        => 0.0,
                    'ph_morning_hours'          => 0.0,
                    'ph_night_hours'            => 0.0,
                    'travel_amount'             => 0.0,
                    'daily'                     => [],
                ];
            }

            $g = &$byGuard[$key];

            $g['morning_hours']          += (float) ($row['morning_hours']          ?? 0);
            $g['night_hours']            += (float) ($row['night_hours']            ?? 0);
            $g['saturday_morning_hours'] += (float) ($row['saturday_morning_hours'] ?? 0);
            $g['saturday_night_hours']   += (float) ($row['saturday_night_hours']   ?? 0);
            $g['sunday_morning_hours']   += (float) ($row['sunday_morning_hours']   ?? 0);
            $g['sunday_night_hours']     += (float) ($row['sunday_night_hours']     ?? 0);
            $g['ph_morning_hours']       += (float) ($row['ph_morning_hours']       ?? 0);
            $g['ph_night_hours']         += (float) ($row['ph_night_hours']         ?? 0);
            $g['travel_amount']          += (float) ($row['travel_time_value']      ?? 0);

            $shiftDate = isset($row['start'])
                ? Carbon::parse($row['start'])->format('Y-m-d')
                : null;

            if ($shiftDate) {
                if (!isset($g['daily'][$shiftDate][$level])) {
                    $g['daily'][$shiftDate][$level] = [
                        'rate_weekday' => 0.0,
                        'rate_night'   => 0.0,
                        'rate_sat'     => 0.0,
                        'rate_sun'     => 0.0,
                        'rate_ph'      => 0.0,
                        'rate_travel'  => 0.0,
                    ];
                }

                $g['daily'][$shiftDate][$level]['rate_weekday'] += (float) ($row['morning_hours']          ?? 0);
                $g['daily'][$shiftDate][$level]['rate_night']   += (float) ($row['night_hours']            ?? 0);
                $g['daily'][$shiftDate][$level]['rate_sat']     += (float) ($row['saturday_morning_hours'] ?? 0) + (float) ($row['saturday_night_hours'] ?? 0);
                $g['daily'][$shiftDate][$level]['rate_sun']     += (float) ($row['sunday_morning_hours']   ?? 0) + (float) ($row['sunday_night_hours']   ?? 0);
                $g['daily'][$shiftDate][$level]['rate_ph']      += (float) ($row['ph_morning_hours']       ?? 0) + (float) ($row['ph_night_hours']       ?? 0);
                $g['daily'][$shiftDate][$level]['rate_travel']  += (float) ($row['travel_time_value']      ?? 0);
            }
        }

        return array_values($byGuard);
    }
}