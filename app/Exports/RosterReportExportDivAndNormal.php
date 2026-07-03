<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;

class RosterReportExportDivAndNormal implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\RosterReport')->generateRosterReportnormalanddivide($request);
        // print_r($data);
        // exit();
        return view('exports.roster_report_simple', [
            'data' => $data,                        
            'report' => $request['report'],

        ]); 
    }

}
