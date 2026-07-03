<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiReportExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return array
     */
    public function sheets(): array
    {
        $reports = ['AwardOverTime','ChargableDailyReport','NonPayableHrs','TrainingHours','NonChargableHours','ChargableWeeklyReport','HrsByEmpReport','SickLeave','CasualHours'];
        $sheets = [];

        foreach ($reports as $report) {
            $exportClass = 'App\Exports\\' . $report . 'Export';
            $sheets[] = app($exportClass);
        }

        return $sheets;
    }
}
