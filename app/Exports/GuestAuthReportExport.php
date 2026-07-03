<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;

class GuestAuthReportExport implements FromView
{
    public function view(): View
    {
        $request = request();
        $data = app('App\Http\Controllers\reports\GuardReport')->getGuestAuthReportData($request);
        return view('exports.guest_auth_report_pdf', [
            'data' => $data,
        ]); 
    }

}
