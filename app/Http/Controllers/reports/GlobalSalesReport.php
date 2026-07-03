<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\GlobalSalesExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Models\Site;
use App\Models\crm\CustomerModel;
use Illuminate\Support\Facades\DB;

class GlobalSalesReport extends Controller
{


    function generateGlobalSalesReport(Request $request)
    {
        if($request->file_type == 'preview'){
            $data = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
        $filename = time() . '_global_sales_report.xlsx';
        Excel::store(new GlobalSalesExport, 'excel/globalsales/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Global Sales Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/globalsales/' . $filename]);
    }


    public function getReportData($request)
    {  

        $query = CustomerModel::query();

        if (isset($request['lead_status']) && !empty($request['lead_status'])) {
            $query->whereIn('lead_status', $request['lead_status']);
        }

        if (isset($request['clientname']) && !empty($request['clientname'])) {
            $query->whereIn('leaad_client_name', $request['clientname']);
        }

        if (isset($request['assigned_opertaion_person']) && !empty($request['assigned_opertaion_person'])) {
            $query->whereIn('assign_operation', $request['assigned_opertaion_person']);
        }

        if (isset($request['agent_ids']) && !empty($request['agent_ids'])) {
            $query->whereIn('saleperson_id', $request['agent_ids']);
        }

        if (isset($request['companies']) && !empty($request['companies'])) {
            $query->whereIn('sub_company', $request['companies']);
        }

        if (isset($request['travel_Data_range']) && $request['travel_Data_range'] != '') {
            $travel_date = $request['travel_Data_range'];
            $travel_date = explode(' - ', $travel_date);
            $travel_from = strtotime(trim(str_replace('-', '/', $travel_date[0])));
            $travel_to = strtotime(trim(str_replace('-', '/', $travel_date[1])));

            $travel_start = date('Y-m-d 00:00', $travel_from);
            $travel_end = date('Y-m-d 23:59', $travel_to);

            $query->where('travel_date', '>=', $travel_start)->where('travel_date', '<=', $travel_end);

        }
        
        if (isset($request['created_date_range']) && $request['created_date_range'] != '') {
            $created_date = $request['created_date_range'];
            $created_date = explode(' - ', $created_date);
            $created_from = strtotime(trim(str_replace('-', '/', $created_date[0])));
            $created_to = strtotime(trim(str_replace('-', '/', $created_date[1])));

            $created_start = date('Y-m-d 00:00', $created_from);
            $created_end = date('Y-m-d 23:59', $created_to);

            $query->where('created_at', '>=', $created_start)->where('created_at', '<=', $created_end);

        }

        if (isset($request['last_update_range']) && $request['last_update_range'] != '') {
            $last_update_date = $request['last_update_range'];
            $last_update_date = explode(' - ', $last_update_date);
            $last_update_from = strtotime(trim(str_replace('-', '/', $last_update_date[0])));
            $last_update_to = strtotime(trim(str_replace('-', '/', $last_update_date[1])));

            $last_update_start = date('Y-m-d 00:00', $last_update_from);
            $last_update_end = date('Y-m-d 23:59', $last_update_to);

            $query->where('updated_at', '>=', $last_update_start)->where('updated_at', '<=', $last_update_end);

        }
        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            $date = explode(' - ', $date);
            $from = strtotime(trim(str_replace('-', '/', $date[0])));
            $to = strtotime(trim(str_replace('-', '/', $date[1])));

            $startDate = date('Y-m-d 00:00', $from);
            $endDate = date('Y-m-d 23:59', $to);

            $query->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);
        }

        $data = $query->with(['CreatedBy', 'HandledBy', 'AssignOperation', 'LeadClientName'])->get();
    
        return $data;
        }
    


}
