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

class AdhocHoursExport implements FromView, WithTitle
{

    public function title(): string
    {
        return 'AdhocHoursReport'; // Set the custom sheet name here
    }

    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\AdhocHrsReport')->getReportData($request);
        return view('exports.adhoc_hours', [
            'data' => $data,
        ]);
    }
}
