<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Models\WelfareCall;
use App\Exports\GreenCallExport;
use App\Exports\SiteLocationExport;
use App\Http\Controllers\Controller;
use App\Models\GuardLeave;
use App\Models\JobRoster;
use App\Models\Guard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Models\Site;
use App\Models\Customer;
use App\Models\GreenCall;
use App\Models\WelfareCallData;
use Illuminate\Support\Facades\DB;

class SiteLocationReport extends Controller
{


    function generateSiteLocationReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_location.xlsx';
        Excel::store(new SiteLocationExport, 'excel/location/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Locations.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/location/' . $filename]);
    }


    public function getReportData($request)
    {  
        $extra_query = '';

        if (isset($request['sites']) && $request['sites'] != '') {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['sites'] as $key => $id) {
                $extra_query .= "sites.`id` = '".$id."'";
                if ($i < sizeof($request['sites']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ")";
        }
       
        $data = Site::join('customers', 'customers.id', '=', 'sites.customer_id')
            ->whereRaw($extra_query)
            ->select('sites.*', 'customers.name as customer_name')
            ->orderBy('sites.id', 'DESC')
            ->get();

        return $data;
        }
}
