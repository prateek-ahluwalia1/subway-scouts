<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;

class RosterReportEmailExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $request['start'] = date('m/d/Y 00:00', strtotime('monday this week'));
        $request['end'] = date('m/d/Y 23:59', strtotime('sunday this week'));
        $request['report'] = 'normal';

        $data = app('App\Http\Controllers\reports\RosterReport')->generateRosterReportExcelSimpleEmail($request);
         return view('exports.roster_report_simple', [
            'data' => $data,
            'report' => $request['report']
        ]); 
    }
}
