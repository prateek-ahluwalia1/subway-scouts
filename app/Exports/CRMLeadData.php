<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithTitle;



class CRMLeadData implements FromView, WithTitle
{

    public function title(): string
    {
        return 'CRM Lead Report';
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $request = request()->all();
        $data = app('App\Http\Controllers\CrmCommonController')->getReportData($request);
        return view('exports.CrmLeadData', [
            'data' => $data,
        ]); 
    }

}
