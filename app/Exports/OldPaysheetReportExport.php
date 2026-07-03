<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class OldPaysheetReportExport implements FromView
{

    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\PaysheetReport')->getCompleteReportData($request);
        // $data = app('App\Http\Controllers\reports\PaysheetReport')->getReportData($request);
        //scouts view page (PaysheetReport)
        //amg view page (complete_report)
        return view('exports.complete_report', [
            'data' => $data,
        ]); 
    }

}
