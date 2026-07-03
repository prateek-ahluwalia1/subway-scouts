<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;

class InductionReportExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\ReportController')->getInductionReportData($request);
        // dd($data);
        return view('exports.InductionReport', [
            'data' => $data,
        ]); 
    }

}
