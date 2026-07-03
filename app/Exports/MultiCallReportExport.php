<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiCallReportExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return array
     */
    public function sheets(): array
    {
        $reports = ['Green','Welfare'];
        $sheets = [];

        foreach ($reports as $report) {
            $exportClass = 'App\Exports\\' . $report . 'Export';
            $sheets[] = app($exportClass);
        }

        return $sheets;
    }
}
