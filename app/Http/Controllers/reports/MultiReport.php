<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\MultiReportExport;
use App\Http\Controllers\Controller;
use App\Models\JobRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class MultiReport extends Controller
{


    function generateMultiReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . 'MultiReport.xlsx';
        Excel::store(new MultiReportExport, 'excel/MultiReport/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Award Overtime.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/MultiReport/' . $filename]);
    }


}
