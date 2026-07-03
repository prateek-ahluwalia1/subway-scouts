<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\MultiCallReportExport;
use App\Http\Controllers\Controller;
use App\Models\JobRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class MultiCallReport extends Controller
{


    function generateMultiCallReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . 'MultiCallReport.xlsx';
        Excel::store(new MultiCallReportExport, 'excel/MultiCallReport/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Award Overtime.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/MultiCallReport/' . $filename]);
    }


}
