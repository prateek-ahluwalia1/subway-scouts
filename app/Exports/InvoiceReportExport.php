<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InvoiceReportExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\reports\InvoiceReport')->getReportData($request);
        // dd($data);
        return view('exports.InvoiceReport', [
            'data' => $data,
        ]); 
    }

}
