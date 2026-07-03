<?php

namespace App\Console\Commands;

use App\Services\Xero\XeroHttpClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * php artisan xero:verify-rates
 *
 * Fetches ALL EarningsRates from Xero and compares them against
 * what you have in .env / config/services.php.
 *
 * Run this FIRST after connecting Xero to confirm your UUIDs are correct.
 * If any show ? MISMATCH, copy the correct UUID from the output into .env.
 *
 * Also clears the rate ID cache so the next pay run uses fresh UUIDs.
 */
class XeroVerifyRatesCommand extends Command
{
    protected $signature   = 'xero:verify-rates {--dump : Dump raw API response for debugging}';
    protected $description = 'List all Xero EarningsRates and verify your .env UUIDs match';

    public function handle(XeroHttpClient $xero): int
    {
        Cache::forget('xero_verified_rate_ids_v2');

        $this->info('Fetching EarningsRates from Xero...');
        $this->newLine();

        try {
            $response = $xero->get('/payroll.xro/1.0/PayItems');
        } catch (\Exception $e) {
            $this->error('Failed to fetch PayItems: ' . $e->getMessage());
            return self::FAILURE;
        }

        // -- Always dump raw structure so we can see exactly what Xero returns --
        // Xero's PayItems response has changed shape between versions.
        // We try multiple known structures and fall back to a deep search.
        if ($this->option('dump')) {
            $this->line('=== RAW RESPONSE (first 3000 chars) ===');
            $this->line(substr(json_encode($response, JSON_PRETTY_PRINT), 0, 3000));
            $this->newLine();
        }

        $xeroRates = $this->extractEarningsRates($response);

        if (empty($xeroRates)) {
            $this->warn('No EarningsRates found in Xero response.');
            $this->warn('Run with --dump to see the raw response:');
            $this->line('  php artisan xero:verify-rates --dump');
            $this->newLine();
            $this->line('Raw response top-level keys: ' . implode(', ', array_keys($response)));
            return self::FAILURE;
        }

        // -- Print all rates from Xero ------------------------------------------
        $this->info('=== ALL EARNINGS RATES IN XERO (' . count($xeroRates) . ' found) ===');
        $this->table(
            ['EarningsRateID (UUID)', 'Name', 'EarningsType', 'RateType'],
            array_map(fn($r) => [
                $r['EarningsRateID'] ?? '—',
                $r['Name']           ?? '—',
                $r['EarningsType']   ?? '—',
                $r['RateType']       ?? '—',
            ], $xeroRates)
        );

        // Build UUID lookup
        $xeroUuids = [];
        foreach ($xeroRates as $rate) {
            if (! empty($rate['EarningsRateID'])) {
                $xeroUuids[$rate['EarningsRateID']] = $rate['Name'];
            }
        }

        // -- Compare against config ---------------------------------------------
        $this->newLine();
        $this->info('=== CONFIG vs XERO VERIFICATION ===');

        $configKeys = [
            'rate_weekday'       => 'XERO_RATE_WEEKDAY',
            'rate_night'         => 'XERO_RATE_NIGHT',
            'rate_sat'           => 'XERO_RATE_SAT',
            'rate_sun'           => 'XERO_RATE_SUN',
            'rate_ph'            => 'XERO_RATE_PH',
            'rate_travel'        => 'XERO_RATE_TRAVEL',
            'rate_reimbursement' => 'XERO_RATE_REIMBURSEMENT',
        ];

        $rows    = [];
        $allGood = true;

        foreach ($configKeys as $configKey => $envKey) {
            $uuid = config("services.xero.{$configKey}");

            if (empty($uuid)) {
                $rows[]  = [$envKey, '(not set)', '—', '? EMPTY'];
                $allGood = false;
                continue;
            }

            if (isset($xeroUuids[$uuid])) {
                $rows[] = [$envKey, $uuid, $xeroUuids[$uuid], '? OK'];
            } else {
                $rows[]  = [$envKey, $uuid, '—', '? NOT FOUND IN XERO'];
                $allGood = false;
            }
        }

        $this->table(['ENV Key', 'UUID in .env', 'Name in Xero', 'Status'], $rows);

        if ($allGood) {
            $this->newLine();
            $this->info('? All EarningsRate UUIDs are correct. You are ready to push pay runs.');
        } else {
            $this->newLine();
            $this->warn('? Some UUIDs are missing or incorrect.');
            $this->warn('Copy the correct EarningsRateID from the top table into your .env file.');
            $this->newLine();
            $this->line('Quick copy — paste these into your .env:');
            foreach ($xeroRates as $rate) {
                $name = strtoupper(str_replace(' ', '_', $rate['Name'] ?? ''));
                $this->line("# {$rate['Name']}");
                $this->line("# XERO_RATE_??? = {$rate['EarningsRateID']}");
            }
        }

        // -- Payroll Calendar check ---------------------------------------------
        $this->newLine();
        $this->info('=== PAYROLL CALENDAR ===');

        try {
            $calRes    = $xero->get('/payroll.xro/1.0/PayrollCalendars');
            $cals      = $calRes['PayrollCalendars'] ?? [];
            $configCal = trim(config('services.xero.payroll_calendar_id'));

            if (empty($cals)) {
                $this->warn('No PayrollCalendars found. Create one in Xero first.');
            } else {
                $calRows = [];
                foreach ($cals as $cal) {
                    $id      = $cal['PayrollCalendarID'] ?? '—';
                    $match   = ($id === $configCal) ? '? IN USE' : '';
                    $calRows[] = [$id, $cal['Name'] ?? '—', $cal['CalendarType'] ?? '—', $match];
                }
                $this->table(['CalendarID', 'Name', 'Type', 'Config Match'], $calRows);

                if (empty(array_filter($calRows, fn($r) => $r[3] === '? IN USE'))) {
                    $this->warn("? XERO_PAYROLL_CALENDAR_ID='{$configCal}' not found in the calendars above.");
                    $this->warn('Update XERO_PAYROLL_CALENDAR_ID in .env with one of the CalendarIDs above.');
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not fetch PayrollCalendars: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Try multiple known Xero response structures for PayItems.
    //
    // Xero PayrollAU /PayItems returns one of these shapes depending on
    // API version / whether pay items exist:
    //
    //   Shape A (most common):
    //     { "PayItems": { "EarningsRates": [...], "DeductionTypes": [...] } }
    //
    //   Shape B (some versions):
    //     { "PayItems": [{ "EarningsRates": [...] }] }
    //
    //   Shape C (empty org):
    //     { "PayItems": [] }
    //
    // We try all shapes and return whichever has EarningsRates.
    // -------------------------------------------------------------------------
    private function extractEarningsRates(array $response): array
    {
        $payItems = $response['PayItems'] ?? null;

        if (empty($payItems)) {
            return [];
        }

        // Shape A: PayItems is an associative array with EarningsRates key
        if (isset($payItems['EarningsRates']) && is_array($payItems['EarningsRates'])) {
            return $payItems['EarningsRates'];
        }

        // Shape B: PayItems is a list of objects, first has EarningsRates
        if (isset($payItems[0]['EarningsRates']) && is_array($payItems[0]['EarningsRates'])) {
            return $payItems[0]['EarningsRates'];
        }

        // Shape C: PayItems is a list, any element might have EarningsRates
        if (is_array($payItems)) {
            foreach ($payItems as $item) {
                if (is_array($item) && isset($item['EarningsRates'])) {
                    return $item['EarningsRates'];
                }
            }
        }

        return [];
    }
}