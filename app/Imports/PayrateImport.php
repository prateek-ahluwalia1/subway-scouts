<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Payrate;
use App\Models\Site;
use Carbon\Carbon;
use \DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PayrateImport implements ToModel, WithHeadingRow
{
    public function model(array $payrate)
    {
        if (empty($payrate['site_name'])) {
            \Log::warning('Skipping row - Missing site_name');
            return null;
        }

        $site = Site::whereRaw('LOWER(site_name) LIKE ?', ['%' . strtolower(trim($payrate['site_name'])) . '%'])
                ->first();

        if (!$site) {
            \Log::warning('Site not found: ' . $payrate['site_name']);
            return null;
        }

        $sitePayrate = null;
        if (!empty($payrate['site_payrate'])) {
            $payrateTitle = preg_replace('/^Level (\d+)/i', 'L$1', trim($payrate['site_payrate']));
            $payrateModel = Payrate::whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($payrateTitle) . '%'])
                                ->first();
            $payrateId = $payrateModel ? $payrateModel->id : null;
        }

        $break = null;
        if (isset($payrate['break'])) {
            $break = in_array(strtolower(trim($payrate['break'])), ['Yes', '1', 'true']) ? 1 : 0;
        }

        if (array_key_exists('payrol', $payrate)) {
            $site->payrol = $payrate['payrol'];
        }

        if (!empty($payrateId)) {
            $site->site_payrate = $payrateId;
        }

        if (array_key_exists('level', $payrate)) {
            $site->level = preg_replace('/[^0-9]/', '', $payrate['level']);
        }

        if (array_key_exists('site_hours', $payrate)) {
            $site->site_hours = $payrate['site_hours'];
        }

        if (!is_null($break)) {
            $site->break = $break;
        }

        if (array_key_exists('break_chargeable', $payrate)) {
            $site->break_chargeable = $payrate['break_chargeable'];
        }

        if (array_key_exists('break_payable', $payrate)) {
            $site->break_payable = $payrate['break_payable'];
        }

        if (array_key_exists('break_deduction_chargeable', $payrate)) {
            $site->break_deduction_chargeable = $payrate['break_deduction_chargeable'];
        }

        // Save all changes
        try {
            $site->save();
            \Log::info('Updated site: ' . $site->site_name, [
                'changes' => $site->getChanges(),
                'payrate_mapping' => [
                    'excel_value' => $payrate['site_payrate'] ?? null,
                    'db_value' => $payrateTitle ?? null,
                    'payrate_id' => $payrateId ?? null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update site: ' . $site->site_name . ' - ' . $e->getMessage());
        }

        return null;
    }
}