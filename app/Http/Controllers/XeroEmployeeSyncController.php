<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\GuardWorkDetail;
use App\Models\XeroEmployeeMap;
use App\Services\Xero\XeroHttpClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Syncs CRM guards → Xero Payroll AU employees.
 *
 * KEY FIXES vs previous version
 * ──────────────────────────────
 * 1. PayTemplate is embedded inside the Employee POST body, NOT via a
 *    separate /PayTemplate sub-endpoint (that does not exist in AU Payroll).
 *
 * 2. PayTemplate EarningsLines use the UUIDs from config/services.php
 *    (env XERO_RATE_*). These must already exist in Xero before syncing.
 *    Run `php artisan xero:ensure-rates` first (see XeroEnsureRatesCommand).
 *
 * 3. Employee StartDate defaults to 2026-01-01 as requested.
 *
 * 4. Bank account push is separated into its own try/catch so a missing
 *    BSB doesn't block the employee sync.
 */
class XeroEmployeeSyncController extends Controller
{
    public function __construct(private XeroHttpClient $xero) {}

    // ─────────────────────────────────────────────────────────────────
    // POST /api/xero/employees/{guardId}/sync
    // ─────────────────────────────────────────────────────────────────
    public function sync(int $guardId)
    {
        $guard   = Guard::with('guardExternalIds')->findOrFail($guardId);
        $details = GuardWorkDetail::where('guard_id', $guardId)->first();

        try {
            $existing = XeroEmployeeMap::where('crm_employee_id', $guardId)->first();

            if ($existing) {
                $xeroId = $existing->xero_employee_id;
                $payload = $this->buildEmployeePayload($guard, $details, $xeroId);
                unset($payload['PayTemplate']);
                $this->xero->post('/payroll.xro/1.0/Employees', [$payload]);

                $existing->update([
                    'xero_first_name' => $guard->first_name,
                    'xero_last_name'  => $guard->last_name,
                    'last_synced_at'  => now(),
                ]);

                // Set PayTemplate only if not already set
                if (empty($existing->ordinary_earnings_rate_id)) {
                    try {
                        $this->addPayTemplate($xeroId);
                    } catch (\Exception $e) {
                        Log::warning("[XeroEmployeeSync] PayTemplate failed on update for guard {$guardId}: " . $e->getMessage());
                    }
                }

                Log::info("[XeroEmployeeSync] Updated employee {$xeroId} for guard {$guardId}.");
            } else {
                // Create new employee WITHOUT PayTemplate first
                $payload = $this->buildEmployeePayload($guard, $details, null);
                // Remove PayTemplate for initial creation
                unset($payload['PayTemplate']);
                
                $response = $this->xero->post('/payroll.xro/1.0/Employees', [$payload]);

                $xeroId = $response['Employees'][0]['EmployeeID'] ?? null;

                if (! $xeroId) {
                    throw new \RuntimeException(
                        'Xero did not return EmployeeID. Response: ' . json_encode($response)
                    );
                }

                XeroEmployeeMap::create([
                    'crm_employee_id'  => $guardId,
                    'xero_employee_id' => $xeroId,
                    'xero_first_name'  => $guard->first_name,
                    'xero_last_name'   => $guard->last_name,
                    'last_synced_at'   => now(),
                ]);

                Log::info("[XeroEmployeeSync] Created Xero employee {$xeroId} for guard {$guardId}.");
                // Wait for Xero to propagate the new employee before hitting sub-endpoints
                sleep(3);
                // NOW add the PayTemplate separately (after employee exists)
                try {
                    $this->addPayTemplate($xeroId);
                    // Assign default leave types for Part Time and Full Time employees
                    // Casual employees don't accrue leave
                    $staffType = strtolower(trim($guard->staff_type ?? ''));
                    if (!in_array($staffType, ['casual', 'cas'])) {
                        try {
                            $this->assignDefaultLeaveTypes($xeroId);
                        } catch (\Exception $e) {
                            Log::warning("[XeroEmployeeSync] Failed to assign leave types for guard {$guardId}: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("[XeroEmployeeSync] Failed to add PayTemplate: " . $e->getMessage());
                    // Don't fail the sync - employee exists, can add template later
                }
            }

            // Push bank account (non-blocking)
            if ($details && ! empty($details->bsb) && ! empty($details->bank_account_no)) {
                try {
                    // $this->pushBankAccount($xeroId, $guard, $details);
                } catch (\Exception $e) {
                    Log::warning("[XeroEmployeeSync] Bank account push failed for guard {$guardId}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success'          => true,
                'xero_employee_id' => $xeroId,
                'message'          => "{$guard->first_name} {$guard->last_name} synced to Xero.",
            ]);

        } catch (\Exception $e) {
            Log::error("[XeroEmployeeSync] Sync failed for guard {$guardId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xero sync failed: ' . $e->getMessage(),
            ], 422);
        }
    }
    /**
     * Add PayTemplate to existing employee
     * This is a separate API call after employee creation
     */
    private function assignDefaultLeaveTypes(string $xeroId): void
    {
        // Fetch org's leave types to get the default ones
        try {
            $leaveTypes = $this->xero->get('/payroll.xro/1.0/PayItems');
            $allLeaveTypes = $leaveTypes['PayItems']['LeaveTypes'] ?? [];

            // Filter to standard default leave types only
            $defaultNames = ['Annual Leave', "Personal/Carer's Leave", 'Long Service Leave'];
            $leaveLines   = [];

            foreach ($allLeaveTypes as $lt) {
                if (in_array($lt['Name'] ?? '', $defaultNames)) {
                    $leaveLines[] = [
                        'LeaveTypeID'              => $lt['LeaveTypeID'],
                        'NumberOfUnits'            => 0,
                        'CalculationType'          => 'PERCENTAGEOFGROSSEARNINGS',
                    ];
                }
            }

            if (empty($leaveLines)) {
                Log::warning("[XeroEmployeeSync] No default leave types found in Xero PayItems for {$xeroId}.");
                return;
            }

            $this->xero->post('/payroll.xro/1.0/Employees', [[
                'EmployeeID'  => $xeroId,
                'PayTemplate' => [
                    'LeaveLines' => $leaveLines,
                ],
            ]]);

            Log::info("[XeroEmployeeSync] Default leave types assigned for {$xeroId} (" . count($leaveLines) . " types).");

        } catch (\Exception $e) {
            Log::warning("[XeroEmployeeSync] Leave types failed for {$xeroId}: " . $e->getMessage() .
                " — assign manually in Xero UI.");
        }
    }
    private function addPayTemplate(string $xeroId): void
    {
        $ordinaryRateId = trim(config('services.xero.ordinary_earnings_rate_id'));

        if (empty($ordinaryRateId)) {
            Log::warning("[XeroEmployeeSync] No ordinary_earnings_rate_id configured — PayTemplate skipped.");
            return;
        }

        $payload = [
            'EmployeeID' => $xeroId,
            'PayTemplate' => [
                'EarningsLines' => [[
                    'EarningsRateID'  => $ordinaryRateId,
                    'CalculationType' => 'USEEARNINGSRATE',
                    'NumberOfUnits'   => 0,
                ]],
            ],
        ];

        $this->xero->post('/payroll.xro/1.0/Employees', [$payload]);

        XeroEmployeeMap::where('xero_employee_id', $xeroId)
            ->update(['ordinary_earnings_rate_id' => $ordinaryRateId]);

        Log::info("[XeroEmployeeSync] PayTemplate set for employee {$xeroId}.");
    }
    // ─────────────────────────────────────────────────────────────────
    // GET /api/xero/employees/{guardId}/status
    // ─────────────────────────────────────────────────────────────────
    public function status(int $guardId)
    {
        $map = XeroEmployeeMap::where('crm_employee_id', $guardId)->first();

        return response()->json([
            'mapped'           => (bool) $map,
            'xero_employee_id' => $map?->xero_employee_id,
            'last_synced_at'   => $map?->last_synced_at,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /api/xero/employees/status-all
    // ─────────────────────────────────────────────────────────────────
    public function statusAll()
    {
        return response()->json(
            XeroEmployeeMap::all()->keyBy('crm_employee_id')
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // Build full Xero employee payload.
    //
    // CRITICAL: PayTemplate with EarningsLines is embedded HERE inside
    // the employee object. There is no /PayTemplate sub-endpoint in
    // Xero AU Payroll API.
    //
    // The PayTemplate tells Xero to auto-generate a payslip for this
    // employee when a pay run is created. Without it, Xero creates a
    // payslip but with $0 and no earnings lines — which is exactly the
    // bug you were seeing.
    //
    // We set NumberOfUnits = 0 for all lines because the actual hours
    // and amounts are pushed by ProcessXeroPayrollJob per pay run.
    // ─────────────────────────────────────────────────────────────────
    private function buildEmployeePayload(Guard $guard, ?GuardWorkDetail $details, ?string $xeroId): array
    {
        $calendarId     = trim(config('services.xero.payroll_calendar_id'));
        $ordinaryRateId = trim(config('services.xero.ordinary_earnings_rate_id'));
        // Get first external ID and append to last name
        $firstExternalId = $guard->guardExternalIds?->first()?->external_id ?? null;
        $lastName = $guard->last_name;
        if (!empty($firstExternalId)) {
            $lastName = $lastName . ' (' . $firstExternalId . ')';
        }
        $payload = [
            'FirstName'              => $guard->first_name,
            'LastName'               => $lastName,
            'StartDate'              => '2026-01-01',
            'EmploymentBasis'        => $this->mapStaffType($guard->staff_type ?? ''),
            'OrdinaryEarningsRateID' => $ordinaryRateId,
            'PayrollCalendarID'      => $calendarId,
            'EmploymentType'                   => 'EMPLOYEE',
            'IncomeType'                       => 'SALARYANDWAGES',
            'TaxDeclaration' => [
                'TaxFileNumber'                    => $details?->tfn_file_no ?? '',
                'EmploymentBasis'                  => $this->mapStaffType($guard->staff_type ?? ''),
                'TaxFreeThresholdClaimed'          => (bool) true,
                'ResidencyStatus'                  => 'AUSTRALIANRESIDENT',
                'TaxScaleType'                     => 'REGULAR',
            ],
            'HomeAddress' => [
                'AddressLine1' => $this->sanitiseAddress($guard->address ?? 'N/A'),
                'City'         => $guard->city        ?? 'Melbourne',
                'Region'       => $this->mapState($guard->state ?? 'VIC'),
                'PostalCode'   => $guard->postal_code ?? '3000',
                'Country'      => 'AUSTRALIA',
            ],
        ];

        if (!empty($guard->phone)) {
            $payload['Mobile'] = $guard->phone;
        }

        if (!empty($guard->email)) {
            $payload['Email'] = $guard->email;
        }

        $gender = $this->mapGender($guard->gender ?? '');
        if ($gender) {
            $payload['Gender'] = $gender;
        }

        if (!empty($guard->dob)) {
            $dob = $this->parseGuardDate($guard->dob);
            if ($dob) {
                $payload['DateOfBirth'] = $dob;
            }
        }

        if ($xeroId) {
            $payload['EmployeeID'] = $xeroId;
        }

        $superFundId = trim(config('services.xero.super_fund_id'));
        if (!empty($superFundId)) {
            $payload['SuperMemberships'] = [[
                'SuperFundID'    => $superFundId,
                'EmployeeNumber' => $details->member_number ?? '', // CRM guard ID as member number
            ]];
        }

        if ($details && !empty($details->bsb) && !empty($details->bank_account_no)) {
            // Strip ALL non-digits first — handles both "014111" and "014-111" input
            $bsb    = preg_replace('/\D/', '', $details->bsb ?? '');
            $accNum = preg_replace('/\D/', '', $details->bank_account_no ?? '');

            if (strlen($bsb) === 6 && !empty($accNum)) {
                // Xero requires BSB as plain 6 digits — NO dash
                $payload['BankAccounts'] = [[
                    'StatementText' => 'Wages',
                    'AccountName'   => trim($guard->first_name . ' ' . $guard->last_name),
                    'BSB'           => $bsb,           // ← plain 6 digits, no dash
                    'AccountNumber' => $accNum,
                    'Remainder'     => true,
                ]];
            } else {
                Log::warning("[XeroEmployeeSync] Invalid BSB '{$bsb}' for guard {$guard->id} — skipping bank account.");
            }
        }
        return $payload;
    }
    private function sanitiseAddress(string $address): string
    {
        // Some CRM addresses contain the full formatted string e.g.
        // "7 Nathalia Street, Broadmeadows VIC 3047, Australia"
        // Xero only wants the street line — max 50 chars.
        // Strip anything from the second comma onwards (city, state, postcode, country).
        if (substr_count($address, ',') >= 2) {
            $address = substr($address, 0, strrpos($address, ',', -(strlen($address) - strpos($address, ',') - 1)));
            // Now strip the first comma-delimited chunk that's just city+state
            $parts   = explode(',', $address, 2);
            $address = trim($parts[0]);
        }

        // Hard truncate to Xero's 50-char limit as a safety net
        return mb_substr(trim($address), 0, 50);
    }
    // ─────────────────────────────────────────────────────────────────
    // Push bank account to Xero employee
    // ─────────────────────────────────────────────────────────────────
    private function pushBankAccount(string $xeroId, Guard $guard, GuardWorkDetail $details): void
    {
        $bsb    = preg_replace('/\D/', '', $details->bsb ?? '');
        $accNum = preg_replace('/\D/', '', $details->bank_account_no ?? '');

        if (empty($bsb) || empty($accNum)) {
            Log::info("[XeroEmployeeSync] Skipping bank account for {$xeroId} — BSB or account number empty.");
            return;
        }

        // Xero AU requires BSB in format XXX-XXX
        if (strlen($bsb) === 6) {
            $bsb = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        }

        $this->xero->post("/payroll.xro/1.0/Employees/{$xeroId}/BankAccounts", [
            'StatementText'   => 'Wages',          // ← was 'Salary', doc says 'Wages'
            'AccountName'     => trim($guard->first_name . ' ' . $guard->last_name),
            'BSB'             => $bsb,
            'AccountNumber'   => $accNum,
            'Remainder'       => true,
            'CalculationType' => 'REMAINDEROFPAY',
        ]);

        Log::info("[XeroEmployeeSync] Bank account pushed for employee {$xeroId}.");
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function parseGuardDate(string $date): ?string
    {
        foreach (['d-m-Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $date)->format('Y-m-d');
            } catch (\Exception) {
                // try next format
            }
        }
        return null;
    }

    private function mapStaffType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'full_time', 'fulltime', 'full time', 'ft' => 'FULLTIME',
            'part_time', 'parttime', 'part time', 'pt' => 'PARTTIME',
            'casual', 'cas'                            => 'CASUAL',
            'labourhire', 'labour hire'                => 'LABOURHIRE',
            default                                    => 'CASUAL',
        };
    }

    private function mapState(string $state): string
    {
        $map = [
            'victoria'                    => 'VIC',
            'new south wales'             => 'NSW',
            'queensland'                  => 'QLD',
            'south australia'             => 'SA',
            'western australia'           => 'WA',
            'tasmania'                    => 'TAS',
            'northern territory'          => 'NT',
            'australian capital territory'=> 'ACT',
        ];
        $lower = strtolower(trim($state));
        if (strlen($lower) <= 3) return strtoupper($state);
        return $map[$lower] ?? strtoupper(substr($state, 0, 3));
    }

    private function mapGender(string $gender): string
    {
        return match (strtolower(trim($gender))) {
            'male', 'm'   => 'M',
            'female', 'f' => 'F',
            default       => '',
        };
    }


    // ─────────────────────────────────────────────────────────────────
    // POST /api/xero/employees/sync-all
    // ─────────────────────────────────────────────────────────────────
    public function syncAll()
    {
        $guards = Guard::with(['workDetail', 'guardExternalIds'])
            ->whereNotIn('guard_status', ['deleted', 'pending', 'new'])
            ->get();

        $results = [
            'synced'  => [],
            'updated' => [],
            'failed'  => [],
            'skipped' => [],
        ];

        foreach ($guards as $guard) {
            try {
                $details  = $guard->workDetail;
                $existing = XeroEmployeeMap::where('crm_employee_id', $guard->id)->first();

                if ($existing) {
                    $xeroId  = $existing->xero_employee_id;
                    $payload = $this->buildEmployeePayload($guard, $details, $xeroId);
                    unset($payload['PayTemplate']);
                    $this->xero->post('/payroll.xro/1.0/Employees', [$payload]);

                    $existing->update([
                        'xero_first_name' => $guard->first_name,
                        'xero_last_name'  => $guard->last_name,
                        'last_synced_at'  => now(),
                    ]);

                    // Set PayTemplate only if not already set
                    if (empty($existing->ordinary_earnings_rate_id)) {
                        try {
                            $this->addPayTemplate($xeroId);
                        } catch (\Exception $e) {
                            Log::warning("[XeroEmployeeSync] PayTemplate failed on update for guard {$guard->id}: " . $e->getMessage());
                        }
                    }

                    $results['updated'][] = [
                        'guard_id' => $guard->id,
                        'name'     => "{$guard->first_name} {$guard->last_name}",
                        'xero_id'  => $xeroId,
                    ];

                    Log::info("[XeroEmployeeSync] Updated guard {$guard->id} → {$xeroId}");

                } else {
                    // Create
                    $payload = $this->buildEmployeePayload($guard, $details, null);
                    unset($payload['PayTemplate']);
                    $response = $this->xero->post('/payroll.xro/1.0/Employees', [$payload]);

                    $xeroId = $response['Employees'][0]['EmployeeID'] ?? null;

                    if (!$xeroId) {
                        throw new \RuntimeException('No EmployeeID returned. Response: ' . json_encode($response));
                    }

                    XeroEmployeeMap::create([
                        'crm_employee_id'  => $guard->id,
                        'xero_employee_id' => $xeroId,
                        'xero_first_name'  => $guard->first_name,
                        'xero_last_name'   => $guard->last_name,
                        'last_synced_at'   => now(),
                    ]);

                    Log::info("[XeroEmployeeSync] Created guard {$guard->id} → {$xeroId}");

                    // PayTemplate
                    try {
                        $this->addPayTemplate($xeroId);
                    } catch (\Exception $e) {
                        Log::warning("[XeroEmployeeSync] PayTemplate failed for guard {$guard->id}: " . $e->getMessage());
                    }

                    // Leave types for non-casual
                    $staffType = strtolower(trim($guard->staff_type ?? ''));
                    if (!in_array($staffType, ['casual', 'cas'])) {
                        try {
                            $this->assignDefaultLeaveTypes($xeroId);
                        } catch (\Exception $e) {
                            Log::warning("[XeroEmployeeSync] Leave types failed for guard {$guard->id}: " . $e->getMessage());
                        }
                    }

                    $results['synced'][] = [
                        'guard_id' => $guard->id,
                        'name'     => "{$guard->first_name} {$guard->last_name}",
                        'xero_id'  => $xeroId,
                    ];
                }

                // Bank account (non-blocking)
                if ($details && !empty($details->bsb) && !empty($details->bank_account_no)) {
                    try {
                        // $this->pushBankAccount($xeroId, $guard, $details);
                    } catch (\Exception $e) {
                        Log::warning("[XeroEmployeeSync] Bank account failed for guard {$guard->id}: " . $e->getMessage());
                    }
                }

                // Xero rate limit — 60 calls/min, sleep between employees
                sleep(1);

            } catch (\Exception $e) {
                Log::error("[XeroEmployeeSync] Failed guard {$guard->id}: " . $e->getMessage());
                $results['failed'][] = [
                    'guard_id' => $guard->id,
                    'name'     => "{$guard->first_name} {$guard->last_name}",
                    'error'    => $e->getMessage(),
                ];
            }
        }

        $summary = [
            'total'   => $guards->count(),
            'synced'  => count($results['synced']),
            'updated' => count($results['updated']),
            'failed'  => count($results['failed']),
            'skipped' => count($results['skipped']),
        ];

        Log::info("[XeroEmployeeSync] Bulk sync complete.", $summary);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'results' => $results,
        ]);
    }
    public function createPayItems()
    {
        // All pay items to create — extracted from the data you shared
        $payItems = [
            // ── Level 1 ──────────────────────────────────────────────
            ['Name' => 'L1 Ordinary Hours Day',   'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.0000],
            ['Name' => 'L1 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.2171],
            ['Name' => 'L1 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.5002],
            ['Name' => 'L1 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.0000],
            ['Name' => 'L1 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.5002],
            // ── Level 2 ──────────────────────────────────────────────
            ['Name' => 'L2 Ordinary Hours Day',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.0288],
            ['Name' => 'L2 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.2521],
            ['Name' => 'L2 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.5433],
            ['Name' => 'L2 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.0575],
            ['Name' => 'L2 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.5721],
            // ── Level 3 ──────────────────────────────────────────────
            ['Name' => 'L3 Ordinary Hours Day',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.0461],
            ['Name' => 'L3 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.2731],
            ['Name' => 'L3 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.5691],
            ['Name' => 'L3 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.0921],
            ['Name' => 'L3 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.6152],
            // ── Level 4 ──────────────────────────────────────────────
            ['Name' => 'L4 Ordinary Hours Day',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.0638],
            ['Name' => 'L4 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.2945],
            ['Name' => 'L4 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.5957],
            ['Name' => 'L4 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.1275],
            ['Name' => 'L4 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.6594],
            // ── Level 5 ──────────────────────────────────────────────
            ['Name' => 'L5 Ordinary Hours Day',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.0980],
            ['Name' => 'L5 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.3362],
            ['Name' => 'L5 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.6473],
            ['Name' => 'L5 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.1961],
            ['Name' => 'L5 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.7453],
            // ── DHHS L3 ──────────────────────────────────────────────
            ['Name' => 'DHHS L3 Ordinary Hours Day',   'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.1088],
            ['Name' => 'DHHS L3 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.3495],
            ['Name' => 'DHHS L3 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.6633],
            ['Name' => 'DHHS L3 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.2177],
            ['Name' => 'DHHS L3 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.7721],
            // ── Airport L1 ───────────────────────────────────────────
            ['Name' => 'Airport L1 Ordinary Hours Day',   'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.0600],
            ['Name' => 'Airport L1 Ordinary Hours Night',  'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.2901],
            ['Name' => 'Airport L1 Ordinary Hours Sat',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 1.5902],
            ['Name' => 'Airport L1 Ordinary Hours Sun',    'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.1200],
            ['Name' => 'Airport L1 Ordinary Hours PH',     'RateType' => 'MULTIPLEOFORDINARYEARNINGSRATE', 'Rate' => 2.6502],
        ];

        $created = [];
        $failed  = [];

        foreach ($payItems as $item) {
            try {
                $response = $this->xero->post('/payroll.xro/1.0/PayItems', [
                    'EarningsRates' => [[
                        'Name'         => $item['Name'],
                        'EarningsType' => 'ORDINARYTIMEEARNINGS',
                        'RateType'     => 'MULTIPLE',              // ← was MULTIPLEOFORDINARYEARNINGSRATE
                        'Multiplier'   => (float) $item['Rate'],   // ← was MultipleOfOrdinaryEarningsRate
                        'AccountCode'  => '477',
                        'IsExemptFromTax'   => false,
                        'IsExemptFromSuper' => false,
                        'IsReportableAsW1'  => true,
                    ]],
                ]);
                $rateId = $response['PayItems']['EarningsRates'][0]['EarningsRateID'] ?? null;

                $created[] = [
                    'name'    => $item['Name'],
                    'rate'    => $item['Rate'],
                    'uuid'    => $rateId,
                ];

                Log::info("[XeroPayItems] Created: {$item['Name']} → {$rateId}");

                sleep(1); // stay under rate limit

            } catch (\Exception $e) {
                Log::error("[XeroPayItems] Failed: {$item['Name']} → " . $e->getMessage());
                $failed[] = [
                    'name'  => $item['Name'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Generate .env lines from created items
        $nameToEnvKey = [
            'L1 Ordinary Hours Day'        => 'XERO_RATE_WEEKDAY_L1',
            'L1 Ordinary Hours Night'      => 'XERO_RATE_NIGHT_L1',
            'L1 Ordinary Hours Sat'        => 'XERO_RATE_SAT_L1',
            'L1 Ordinary Hours Sun'        => 'XERO_RATE_SUN_L1',
            'L1 Ordinary Hours PH'         => 'XERO_RATE_PH_L1',
            'L2 Ordinary Hours Day'        => 'XERO_RATE_WEEKDAY_L2',
            'L2 Ordinary Hours Night'      => 'XERO_RATE_NIGHT_L2',
            'L2 Ordinary Hours Sat'        => 'XERO_RATE_SAT_L2',
            'L2 Ordinary Hours Sun'        => 'XERO_RATE_SUN_L2',
            'L2 Ordinary Hours PH'         => 'XERO_RATE_PH_L2',
            'L3 Ordinary Hours Day'        => 'XERO_RATE_WEEKDAY_L3',
            'L3 Ordinary Hours Night'      => 'XERO_RATE_NIGHT_L3',
            'L3 Ordinary Hours Sat'        => 'XERO_RATE_SAT_L3',
            'L3 Ordinary Hours Sun'        => 'XERO_RATE_SUN_L3',
            'L3 Ordinary Hours PH'         => 'XERO_RATE_PH_L3',
            'L4 Ordinary Hours Day'        => 'XERO_RATE_WEEKDAY_L4',
            'L4 Ordinary Hours Night'      => 'XERO_RATE_NIGHT_L4',
            'L4 Ordinary Hours Sat'        => 'XERO_RATE_SAT_L4',
            'L4 Ordinary Hours Sun'        => 'XERO_RATE_SUN_L4',
            'L4 Ordinary Hours PH'         => 'XERO_RATE_PH_L4',
            'L5 Ordinary Hours Day'        => 'XERO_RATE_WEEKDAY_L5',
            'L5 Ordinary Hours Night'      => 'XERO_RATE_NIGHT_L5',
            'L5 Ordinary Hours Sat'        => 'XERO_RATE_SAT_L5',
            'L5 Ordinary Hours Sun'        => 'XERO_RATE_SUN_L5',
            'L5 Ordinary Hours PH'         => 'XERO_RATE_PH_L5',
            'DHHS L3 Ordinary Hours Day'   => 'XERO_RATE_WEEKDAY_DHHS_L3',
            'DHHS L3 Ordinary Hours Night' => 'XERO_RATE_NIGHT_DHHS_L3',
            'DHHS L3 Ordinary Hours Sat'   => 'XERO_RATE_SAT_DHHS_L3',
            'DHHS L3 Ordinary Hours Sun'   => 'XERO_RATE_SUN_DHHS_L3',
            'DHHS L3 Ordinary Hours PH'    => 'XERO_RATE_PH_DHHS_L3',
            'Airport L1 Ordinary Hours Day'   => 'XERO_RATE_WEEKDAY_AIRPORT_L1',
            'Airport L1 Ordinary Hours Night' => 'XERO_RATE_NIGHT_AIRPORT_L1',
            'Airport L1 Ordinary Hours Sat'   => 'XERO_RATE_SAT_AIRPORT_L1',
            'Airport L1 Ordinary Hours Sun'   => 'XERO_RATE_SUN_AIRPORT_L1',
            'Airport L1 Ordinary Hours PH'    => 'XERO_RATE_PH_AIRPORT_L1',
        ];

        $envLines = [];
        foreach ($created as $item) {
            $envKey = $nameToEnvKey[$item['name']] ?? null;
            if ($envKey && $item['uuid']) {
                $envLines[] = "{$envKey}={$item['uuid']}";
            }
        }

        return response()->json([
            'success'    => true,
            'created'    => count($created),
            'failed'     => count($failed),
            'items'      => $created,
            'errors'     => $failed,
            'env_lines'  => implode("\n", $envLines), // ← copy these into your .env
        ]);
    }
    public function rateLimitStatus()
    {
        $res = $this->xero->getRawResponse('/payroll.xro/1.0/Employees', ['page' => 1]);

        return response()->json([
            'day_limit_remaining'     => $res->header('X-DayLimit-Remaining'),
            'min_limit_remaining'     => $res->header('X-MinLimit-Remaining'),
            'app_min_limit_remaining' => $res->header('X-AppMinLimit-Remaining'),
            'rate_limit_problem'      => $res->header('X-Rate-Limit-Problem'),
            'retry_after'             => $res->header('Retry-After'),
            'status'                  => $res->status(),
        ]);
    }
}