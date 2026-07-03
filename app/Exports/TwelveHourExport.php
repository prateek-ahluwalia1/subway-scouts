<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithTitle;

class TwelveHourExport implements FromView, WithTitle
{

    public function title(): string
    {
        return '12HoursShiftReport'; // Set the custom sheet name here
    }
  
    public function view(): View
    {
        $request = request()->all();
        $data['data'] = app('App\Http\Controllers\reports\TwelveHrsShiftReport')->getReportData($request);
        $data['date'] = $request['date'];
        return view('exports.TwelveHoursShiftReport', [
            'data' => $data,
        ]);
    }
}
