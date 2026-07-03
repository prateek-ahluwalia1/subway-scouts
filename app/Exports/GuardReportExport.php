<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;

class GuardReportExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\GuardReport')->getReportData($request);
        // dd($data);
        return view('exports.GuardReport', [
            'data' => $data,
        ]); 
    }

}
