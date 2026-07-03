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

class NonChargableHoursExport implements FromView, WithDrawings, WithTitle
{

    public function title(): string
    {
        return 'NonChargableHoursReport'; // Set the custom sheet name here
    }

    /**
     * @return \Illuminate\Support\Collection
     */

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/marketing/logo green .png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('F1');

        return $drawing;
    }

    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\NonChargableHoursReport')->getReportData($request);
        return view('exports.NonChargableHoursReport', [
            'data' => $data,
        ]);
    }
}
