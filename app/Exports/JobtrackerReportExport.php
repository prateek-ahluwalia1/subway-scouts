<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class JobtrackerReportExport implements FromView
{

    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\ReportController')->timesheet_search($request);

        return view('exports.jobtracker', [
            'data' => $data,
        ]); 
    }

}
