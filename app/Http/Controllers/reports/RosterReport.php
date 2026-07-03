<?php

namespace App\Http\Controllers\reports;

use App\Exports\RosterReportExportNormal;
use App\Exports\RosterReportExportDivAndNormal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\RosterReportExport;
use App\Exports\RosterReportEmailExport;
use App\Exports\SigninoutReport;
use Carbon\Carbon;
use Mail;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use App\Models\JobRoster;

class RosterReport extends Controller
{
	function generateRosterReport(Request $request)
	{
		$filename = time().'_roster_report.xlsx';  
		Excel::store(new RosterReportExport, 'excel/roster/'.$filename, 'excels');
		return response()->json(['success' =>  true, 'message' => 'Roster Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/roster/'.$filename]);  
	}
    
	function generateRosterReportEmail(Request $request)
	{
		$filename = time().'_roster_report.xlsx';  
		Excel::store(new RosterReportEmailExport, 'excel/roster/'.$filename, 'excels');

		// File path and public URL
		$file_path = public_path('excel/roster/'.$filename);
		$file_url = url('excel/roster/'.$filename);

		$data["email"] = "operations@amgsecurity.com.au";
		$data["title"] = "Roster Report";
		$logo1 = 'https://amgsystem.com.au/uploads/amg.png';

		$body = 'Hello ,<br><br>';
		$body .= 'Here is the Roster Report<br><br>';

		$body .= '<a href="' . $file_url . '" style="display:inline-block;padding:10px 20px;background-color:#007BFF;color:#fff;text-decoration:none;border-radius:5px;" target="_blank">Download Roster Report</a><br><br>';

		$body .= '<div style="padding-bottom: 10px">Kind regards,<br>';
		$body .= '<span style="color:blue;">National Operation Center<br>1300 613 975</span><br><br>';
		$body .= '<span style="font-size:large;font-weight:bold;color:black;">AMG PTY LTD<br>NOC: 1300 613 975<br>M: 0487 966 9778<br>P.O Box 6155 Point Cook Vic 3030</span><br>';
		$body .= '<span style="font-size:small;font-weight:bold;">E: operations@amgsecurity.com.au</span><br>';
		$body .= '<img src="' . $logo1 . '" style="width:20%;float:left;"/>';

		$data["body"] = $body;

		Mail::send('mail.rosterEmail', $data, function($message) use ($data, $file_path) {
			$message->from('no-reply@amgsystem.com.au', 'AMG Security')
				->to($data["email"])
				->subject($data["title"])
				->attach($file_path);
		});

		dd('Mail sent successfully');
	}

	function generateRosterReportNormal(Request $request)
	{
		$filename = time().'_roster_report.xlsx';  
		Excel::store(new RosterReportExportNormal, 'excel/roster/'.$filename, 'excels');
		return response()->json(['success' =>  true, 'message' => 'Roster Normal Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/roster/'.$filename]);
		//return Excel::download(new RosterReportExportNormal, 'roster_report.xlsx');  
	}
    #copy to amg roster report
	function generateRosterReportDivNormal(Request $request)
	{
		$filename = time().'_roster_report.xlsx';  
		Excel::store(new RosterReportExportDivAndNormal, 'excel/roster/'.$filename, 'excels');
		return response()->json(['success' =>  true, 'message' => 'Roster Normal Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/roster/'.$filename]);
		//return Excel::download(new RosterReportExportNormal, 'roster_report.xlsx');  
	}

	function generateRosterReportnormalanddivide($request)
	{
		ini_set('memory_limit', '-1');

		$start_str = strtotime($request['start']);
		$end_str = strtotime($request['end']);

	$query = DB::table('job_rosters')
			->join('sites', 'sites.id', '=', 'job_rosters.site_id')
			->join('customers', 'customers.id', '=', 'sites.customer_id')
			->leftJoin('guards', 'guards.id', '=', 'job_rosters.guard_id')
			// ->leftJoin('guard_external_ids as gis', 'gis.guard_id', '=', 'guards.id')
			// ->leftJoin('guard_external_ids as gi', 'gi.customer_id', '=', 'sites.customer_id')
			->where('job_rosters.start', '>=', date('Y-m-d H:i', $start_str))
			->where('job_rosters.start', '<=', date('Y-m-d H:i', $end_str))
			// ->where('job_rosters.guard_id', '!=', null)
			->where('job_rosters.site_id', '!=', null)
			->whereNull('job_rosters.deleted_at');
		// Filter by customers
		if (isset($request['customers']) && is_array($request['customers'])) {
			$customers = $request['customers'];
			$query->where(function ($q) use ($customers) {
				foreach ($customers as $index => $cId) {
					$index == 0 ? $q->where('sites.customer_id', $cId) : $q->orWhere('sites.customer_id', $cId);
				}
			});
		}
		// Filter by site IDs
		if (!empty($request['site_id']) && $request['site_id'] !== 'undefined') {
			$site_ids = $request['site_id'];
			$query->where(function ($q) use ($site_ids) {
				foreach ($site_ids as $index => $id) {
					$index == 0 ? $q->where('job_rosters.site_id', $id) : $q->orWhere('job_rosters.site_id', $id);
				}
			});
		}
		$query->orderBy('job_rosters.start', 'ASC');
		$data = $query->select(
			'job_rosters.*',
			'guards.name',
			'guards.first_name',
			'guards.last_name',
			'sites.level',
			'sites.site_name',
			'sites.site_description',
			'guards.phone',
			'guards.suburb',
			'guards.email',
			// 'gis.external_id',
			'customers.name as customer_name',
			'customers.id as customer_id'
		)->get();
	// dd($data);
		$new_data = [];

		foreach ($data as $d) {
			$d->id1 = 'N/A';
			$d->id2 = 'N/A';

			$ids = DB::table('guard_external_ids')->where('guard_id', $d->guard_id)->where('customer_id', $d->customer_id)->get();

			foreach ($ids as $id) {
				if (preg_match('/AMG/i', $id->external_id)) {
					$d->id1 = $id->external_id;
				} elseif ($id->external_id > 0) {
					$d->id2 = $id->external_id;
				}
			}

			// Handle divide report type
			if (isset($request['report']) && $request['report'] == 'divide') {
				$job_hours = $this->getShiftHours(
					date('m/d/Y H:i', strtotime($d->start)),
					date('m/d/Y H:i', strtotime($d->end))
				);

				$job_hours['morning'] = $this->convertIntoWhole($job_hours['morning']);
				$job_hours['night'] = $this->convertIntoWhole($job_hours['night']);
				$job_hours['saturday_morning'] = $this->convertIntoWhole($job_hours['saturday_morning']);
				$job_hours['saturday_night'] = $this->convertIntoWhole($job_hours['saturday_night']);
				$job_hours['sunday_morning'] = $this->convertIntoWhole($job_hours['sunday_morning']);
				$job_hours['sunday_night'] = $this->convertIntoWhole($job_hours['sunday_night']);
				$job_hours['ph_morning'] = $this->convertIntoWhole($job_hours['ph_morning']);
				$job_hours['ph_night'] = $this->convertIntoWhole($job_hours['ph_night']);

				$d->job_hours = $job_hours;
			}

			$new_data[] = $d;
		}

		return $new_data;
}

# NEW
function generateRosterReportExcelSimple($request)
{
    
    ini_set('memory_limit', '-1');
    $start_str = strtotime($request['start']);
    $end_str = strtotime($request['end']);
    $logo = '';

    $customers = $request['customers'];

	$customers_id = DB::table('sites')->whereIn('id', $request['site_id'])->select('customer_id')->groupBy('customer_id')->get();
	
	
    $pdf = '';
    foreach ($customers_id as $key => $cust) {
        $customer = DB::table('customers')->where('id', $cust->customer_id)->select('name')->first();
				$data = DB::table('sites')->where('customer_id', $cust->customer_id)->whereIn('id', $request['site_id'])->select('id');
			
            //$data = DB::table('sites')->where('customer_id', $cust)->select('id');  

            $sites= $data->get();
        $pdf .= '
  <table style="width:100%;">
  <thead>
  <tr>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="text-align: center; font-size:22px; font-weight:bold; border:1px solid black; background-color:yellow;border:1px solid black;">
  <div style="text-align: center; font-size:18px;">Roster Report '.$customer->name.' - Week starting '.date('D, M d, Y H:i', $start_str).' (Planned)</div>
  </th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  <th style="width:200px;background-color:yellow;border:1px solid black;"></th>
  </tr>
  </thead>
  </table>';
  
  $datediff = $end_str - $start_str;
  $counter = 0;
  $guards_array = array();
  $diff = round($datediff / (60 * 60 * 24));
  $total_hours[0] = array(
    0 => 0,
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
    6 => 0,
    7 => 0
);
// dd($sites);
  foreach ($sites as $key => $site_id) {
      $day_start = $start_str;
      $day_end = $start_str + (60*60*24);
      $days_td = array();
      $max_shifts_in_day = 0;
      $site = DB::table('sites')->where('id', $site_id->id)->select('site_name', 'site_description')->first();
        $unique_guards = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '>=', date('Y-m-d 00:00:00', $day_start))->where('start', '<=', date('Y-m-d 00:00:00', ($day_start + strtotime('+7 days'))))->orderBy('start', 'ASC')->groupBy('guard_id')->where('deleted_at', null)->select('guard_id')->get();
        // dd($unique_guards, 'first check');
    foreach($unique_guards as $ug){
        if($ug->guard_id == null || $ug->guard_id == '')
        {
            $ug->guard_id = 0;
        }
      for ($i=0; $i < $diff; $i++) {
          $days_td[$ug->guard_id][$i] = array(); 
            if ($request['report'] == 'divide') {
        //   dd($days_td[$ug->guard_id][$i], 'third if check');

        // check if any shift ends in this time
          $shifts_ends_in_this_day = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '<', date('Y-m-d 00:00:00', $day_start))->where('end','>', date('Y-m-d 00:00:00', $day_start))->where('guard_id', $ug->guard_id)->where('deleted_at', null)->orderBy('start', 'ASC')->get();

          $shifts_start_in_this_day = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '>=', date('Y-m-d 00:00:00', $day_start))->where('start', '<=', date('Y-m-d 00:00:00', $day_end))->where('guard_id', $ug->guard_id)->where('deleted_at', null)->orderBy('start', 'ASC')->get();

          $all_shifts = $shifts_ends_in_this_day->merge($shifts_start_in_this_day);
      }else{
        //   dd(date('Y-m-d 00:00', $day_start), $site_id->id, date('Y-m-d 23:59', $day_start), $ug->guard_id);
        $all_shifts = DB::table('job_rosters')->where('roster_id', $request['roster_id'])->where('site_id', $site_id->id)->where('start', '>=', date('Y-m-d 00:00', $day_start))->where('start', '<=', date('Y-m-d 23:59', $day_start))->orderBy('start', 'ASC')->where('deleted_at', null)->where('guard_id', $ug->guard_id)->get();
        //   dd($all_shifts, 'fourth else check');
      }

          foreach ($all_shifts as $all_shift) 
          {
            $guard = DB::table('guards')->where('guards.id', $all_shift->guard_id)
				->join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')->where('document_type', 'security_license')
				->select('guards.id as id', 'guards.first_name as first_name', 'guards.middle_name as middle_name', 'guards.last_name as last_name','guards.phone as phone', 'guards.suburb as suburb', 'guards_documents.document_no as security_license_number','guards_documents.document_expire as security_license_expiration')->first();
				if (!empty($guard) && !isset($guards_array[$guard->id])) {
					$guards_array[$guard->id] = array(
					'name' => $guard->first_name.' '.$guard->middle_name.' '.$guard->last_name, 
					'id' => $guard->id,
					'phone' => $guard->phone,
					'suburb' => $guard->suburb,
					'security_license_expiration' => usaToAus($guard->security_license_expiration),
					'security_license_number' => $guard->security_license_number,
    				);
    			}
				
            $all_shift->start = strtotime($all_shift->start); 
            $all_shift->end = strtotime($all_shift->end);
            $diff_hour = $all_shift->end - $all_shift->start;
            $hours = $diff_hour / ( 60 * 60 );
            $total_hours_count = explode('.', $hours);
            if (sizeof($total_hours_count) > 1 ) {
              $partial = '.'.$total_hours_count[1];
                  if ($partial < 0.1) {
                    $hours = $total_hours_count[0];
                }
                if ($partial < 0.27 && $partial > 0.1) {
                    $hours = $total_hours_count[0].'.25';
                }
                if ($partial > 0.27 && $partial < 0.52) {
                    $hours = $total_hours_count[0].'.5';
                }
                if ($partial > 0.52 && $partial < 0.77) {
                    $hours = $total_hours_count[0].'.75';
                }
                if ($partial > 0.77 && $partial < 1) {
                    $hours = $total_hours_count[0]+ 1;
                }
            }

            $days_td[$ug->guard_id][$i][] = array(
                'guard_name' => !empty($guard) ? $guard->first_name : null,
                'start' => date('H:i a', $all_shift->start),
                'end' => date('H:i a', $all_shift->end),
                'ID' => $all_shift->id,
                'day_start' => date('m/d/Y H:i a', $day_start),
                'day_end' => date('m/d/Y H:i a', $day_end),
                'hours' => $hours,
                'training' => $all_shift->training
            );
        }
    $day_start = $day_end + 1;
    $day_end = $day_end + (60*60*24);
    if (count($days_td[$ug->guard_id][$i]) > $max_shifts_in_day) {
      $max_shifts_in_day = count($days_td[$ug->guard_id][$i]);
  }

}
$day_start = $start_str;
$day_end = $start_str + (60*60*24);
}

    // dd($days_td);
// print_r('<pre>');
// print_r($days_td);
// exit();
$total_hours[$site_id->id] = array(
    0 => 0,
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
    6 => 0
);
// dd(!empty($days_td) , count($days_td) > 0 , $max_shifts_in_day > 0, !empty($days_td) && count($days_td) > 0 && $max_shifts_in_day > 0);
if (!empty($days_td) && count($days_td) > 0 && $max_shifts_in_day > 0) {
    // dd('inif');
    $site_name = preg_replace('/[^A-Za-z0-9]+/', ' ', $site->site_name);
    if($site->site_description != '')
    {
        $site_name .= ' - '. htmlspecialchars($site->site_description);
    }
$pdf .= '
  <table style="width:100%;background-color:#CCC0DA;">
  <thead>
  <tr>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>

  <th style="text-align: center; font-size:22px; font-weight:bold; border:1px solid black; background-color:#CCC0DA;">
  <div style="text-align: center; font-size:18px;">'.$site_name.'</div>
  </th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>
  <th style="width:170px;background-color:#CCC0DA;border:1px solid black;"></th>

  </tr>
  </thead>
  </table>';
$pdf .='<table style="width:100%;">
  <thead>
  <tr>
  <th style="border:1px solid #000000;width: 20px;font-size:15px;background-color:#F2DCDB;">Staff\'s
  Name
  </th>';
  for ($i=$start_str; $i < $end_str;) { 
      $pdf .= '<th style="border:1px solid #000000;width: 20px;font-size:15px;background-color:#F2DCDB;">'.date('D M d', $i).'</th>';
      $i = $i + (60*60*24);
  }
  $pdf .='<th style="border:1px solid #000000;width: 20px; font-size:15px; background-color:#F2DCDB;">
  Total Hrs | Wages
  </th>
  </tr></thead>
<tbody>';
}
$c = 0;
foreach($days_td as $key => $value){
    // dd($value, 'valu');
    $background_color = '#DCE6F1';
    if ($c % 2 == 0) {
        $background_color = '';
    }
    // if ($max_shifts_in_day > 1) {
    //     $max_shifts_in_day = 1;
    // }
// for ($i=0; $i < $max_shifts_in_day; $i++) { 
// dd($guards_array);
for ($i=0; $i < $max_shifts_in_day; $i++) {
if (isset($guards_array[$key]['name'])) {
	if(isset($days_td[$key][0][$i]['start']) || isset($days_td[$key][1][$i]['start']) || isset($days_td[$key][2][$i]['start']) || isset($days_td[$key][3][$i]['start']) || isset($days_td[$key][4][$i]['start']) || isset($days_td[$key][5][$i]['start']) || isset($days_td[$key][6][$i]['start'])){
		$pdf .= '<tr>';
		  
		$pdf .= '<td style="border:1px solid #000000; background-color:'.$background_color.'; width:170px;">'.(isset($guards_array[$key]['name']) ? $guards_array[$key]['name'] : 'N/A').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][0][$i])) ? $days_td[$key][0][$i]['start']. ' - '. $days_td[$key][0][$i]['end'].'('.$days_td[$key][0][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][0][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][1][$i])) ? $days_td[$key][1][$i]['start']. ' - '. $days_td[$key][1][$i]['end'].'('.$days_td[$key][1][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][1][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][2][$i])) ? $days_td[$key][2][$i]['start']. ' - '. $days_td[$key][2][$i]['end'].'('.$days_td[$key][2][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][2][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][3][$i])) ? $days_td[$key][3][$i]['start']. ' - '. $days_td[$key][3][$i]['end'].'('.$days_td[$key][3][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][3][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][4][$i])) ? $days_td[$key][4][$i]['start']. ' - '. $days_td[$key][4][$i]['end'].'('.$days_td[$key][4][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][4][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][5][$i])) ? $days_td[$key][5][$i]['start']. ' - '. $days_td[$key][5][$i]['end'].'('.$days_td[$key][5][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][5][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';">'.((isset($days_td[$key][6][$i])) ? $days_td[$key][6][$i]['start']. ' - '. $days_td[$key][6][$i]['end'].'('.$days_td[$key][6][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][6][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
		$pdf .= '<td style="border:1px solid #000000;width:170px;background-color:'.$background_color.';"></td>';
		$total_hours[$site_id->id][0] = $total_hours[$site_id->id][0] + (isset($days_td[$key][0][$i]) ? $days_td[$key][0][$i]['hours'] : 0);
		$total_hours[$site_id->id][1] = $total_hours[$site_id->id][1] + (isset($days_td[$key][1][$i]) ? $days_td[$key][1][$i]['hours'] : 0); 
		$total_hours[$site_id->id][2] = $total_hours[$site_id->id][2] + (isset($days_td[$key][2][$i]) ? $days_td[$key][2][$i]['hours'] : 0); 
		$total_hours[$site_id->id][3] = $total_hours[$site_id->id][3] + (isset($days_td[$key][3][$i]) ? $days_td[$key][3][$i]['hours'] : 0); 
		$total_hours[$site_id->id][4] = $total_hours[$site_id->id][4] + (isset($days_td[$key][4][$i]) ? $days_td[$key][4][$i]['hours'] : 0); 
		$total_hours[$site_id->id][5] = $total_hours[$site_id->id][5] + (isset($days_td[$key][5][$i]) ? $days_td[$key][5][$i]['hours'] : 0); 
		$total_hours[$site_id->id][6] = $total_hours[$site_id->id][6] + (isset($days_td[$key][6][$i]) ? $days_td[$key][6][$i]['hours'] : 0); 
	  
		$pdf .= '</tr>';
		}
}
}
$c++;
}
if (($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6]) > 0) {
   $pdf .= '<tr>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">Sub Total Hrs | Wages</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][0].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][1].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][2].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][3].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][4].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][5].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][6].'</td>';
   $pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6]).'</td>';
   $pdf .= '</tr>';

   $total_hours[0] = array(
    0 => $total_hours[0][0] + $total_hours[$site_id->id][0],
    1 => $total_hours[0][1] + $total_hours[$site_id->id][1],
    2 => $total_hours[0][2] + $total_hours[$site_id->id][2],
    3 => $total_hours[0][3] + $total_hours[$site_id->id][3],
    4 => $total_hours[0][4] + $total_hours[$site_id->id][4],
    5 => $total_hours[0][5] + $total_hours[$site_id->id][5],
    6 => $total_hours[0][6] + $total_hours[$site_id->id][6],
    7 => $total_hours[0][7] + ($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6])
);
}
if (!empty($days_td) && count($days_td) > 0  && $max_shifts_in_day > 0) {
$pdf .='</tbody></table>';
}
}


if (!empty($guards_array)) {

$pdf .='<table style="width:100%;"><tbody>
<tr>
<td style="border:1px solid #000000; background-color:#D8E4BC;">
<div style="text-align: center;" class="title">Guard Details</div>
</td>
</tr></tbody></table>
<table style="width:100%;">
<thead>
<tr>
<th style="border:1px solid #000000;background-color:#C5D9F1;width:170px">
Staff name
</th>
<th style="border:1px solid #000000;background-color:#C5D9F1;width:170px">
Staff Phone
</th>
<th style="border:1px solid #000000;background-color:#C5D9F1;width:170px">
Suburb
</th>
<th style="border:1px solid #000000;background-color:#C5D9F1;width:170px">
Security License
</th>
<th style="border:1px solid #000000;background-color:#C5D9F1;width:170px">
Security License Expiry
</th>
</tr></thead><tbody>
';
$c = 0;
foreach ($guards_array as $key => $guard) {
    $background_color = '#DCE6F1';
    if ($c % 2 == 0) {
        $background_color = '';
    }
   $pdf .='<tr>
   <td style="border:1px solid #000000;background-color:'.$background_color.';width:170px">'.$guard['name'].'</td>
   <td style="border:1px solid #000000;background-color:'.$background_color.';width:170px">'.$guard['phone'].'</td>
   <td style="border:1px solid #000000;background-color:'.$background_color.';width:170px">'.$guard['suburb'].'</td>
   <td style="border:1px solid #000000;background-color:'.$background_color.';width:170px">'.$guard['security_license_number'].'</td>
   <td style="border:1px solid #000000;background-color:'.$background_color.';width:170px">'.$guard['security_license_expiration'].'</td>
   </tr>';
   $c++;
}
$pdf .= '</tbody></table>';
}
}



// $pdf .= '</div>
// </div></body></html>';
// $pdf_data = $pdf;
// return View('admin/report/excel',compact('pdf_data'));
// echo $pdf;
// exit();
return $pdf;
}

function generateRosterReportExcelSimpleEmail($request)
{
    
    ini_set('memory_limit', '-1');
    $start_str = strtotime($request['start']);
    $end_str = strtotime($request['end']);
    $query = DB::table('job_rosters')
    ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->join('customers', 'customers.id', '=', 'sites.customer_id')
    ->leftJoin('guards', 'guards.id', '=', 'job_rosters.guard_id')
    ->where('job_rosters.start', '>=', date('Y-m-d H:i', $start_str))
    ->where('job_rosters.start', '<=', date('Y-m-d H:i', $end_str))
    ->whereNull('job_rosters.deleted_at');
    $query->orderBy('start', 'ASC');
    
    $data = $query->select('job_rosters.*', 'guards.name', 'guards.first_name', 'guards.last_name', 'sites.level', 'sites.site_name', 'sites.site_description', 'guards.phone', 'guards.suburb', 'guards.email', 'customers.name as customer_name')->get();

    return $data;
}


	function convertIntoWhole($hours)
{
    $total_hours = explode('.', $hours);
    if (sizeof($total_hours) > 1 ) {
      $partial = '.'.$total_hours[1];
      if ($partial < 0.1) {
        $hours = $total_hours[0];
    }
    if ($partial < 0.27 && $partial > 0.1) {
        $hours = $total_hours[0].'.25';
    }
    if ($partial > 0.27 && $partial <= 0.52) {
        $hours = $total_hours[0].'.5';
    }
    if ($partial > 0.52 && $partial < 0.77) {
        $hours = $total_hours[0].'.75';
    }
    if ($partial > 0.77 && $partial < 1) {
        $hours = $total_hours[0]+ 1;
    }
}
return $hours;
}



function generateRosterReportExcelNew($request)
{
		ini_set('memory_limit', '-1');
		if (isset($request['date']) && $request['date'] != '') {
			$date = $request['date'];
			$date = explode(' - ', $date);
			$start_str = strtotime(trim(str_replace('-', '/', $date[0])));
			$end_str = strtotime(trim(str_replace('-', '/', $date[1])));
		}else{
			$start_str = time();
			$end_str = time() - (60*60*24*14);
		}
		$logo = '';
		$customers = $request['customer_id'];
		$pdf = '';
		foreach ($customers as $key => $cust) {
			$customer = DB::table('customers')->where('id', $cust)->select('name')->first();
			$data = DB::table('sites')->where('customer_id', $cust)->select('id');
			if (isset($request['search_value']) && $request['search_value'] != 'undefined' && !empty($request['search_value'])) {
				$search_value = json_decode($request['search_value'], true);
				$data->where(function ($query1) use ($search_value){
					$i = 0;
					foreach($search_value as $index) {
						if ($i == 0) {
							$query1->where('id', $index);
						}else{
							$query1->orWhere('id', $index);
						}
						$i++;
					}
				});
			}  
			$sites= $data->get();
			$pdf .= '
			<table style="width:100%;">
			<thead>
			<tr>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="text-align: center; font-size:22px; font-weight:bold; border:1px solid black; background-color:yellow;border:1px solid black;">
			<div style="text-align: center; font-size:18px;">Roster Report '.$customer->name.' - Week starting '.date('D, M d, Y H:i', $start_str).' (Planned)</div>
			</th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			<th style="width:200px;background-color:yellow;border:1px solid black;"></th>
			</tr>
			</thead>
			</table>';

			$datediff = $end_str - $start_str;
			$counter = 0;
			$guards_array = array();
			$diff = round($datediff / (60 * 60 * 24));
			$total_hours[0] = array(
				0 => 0,
				1 => 0,
				2 => 0,
				3 => 0,
				4 => 0,
				5 => 0,
				6 => 0,
				7 => 0
			);
			foreach ($sites as $key => $site_id) {
				$day_start = $start_str-1;
				$day_end = $start_str + (60*60*24)-1;
				$days_td = array();
				$max_shifts_in_day = 0;
				$site = DB::table('sites')->where('id', $site_id->id)->select('site_name', 'site_description')->first();
				$unique_guards = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '>=', date('Y-m-d 00:00:00', $day_start))->where('start', '<=', date('Y-m-d 00:00:00', ($day_start + strtotime('+7 days'))))->select('guard_id')->groupBy('guard_id')->get();
        		// dd($site_id);
				foreach($unique_guards as $ug){
					if($ug->guard_id == null || $ug->guard_id == '')
					{
						$ug->guard_id = 0;
					}
					for ($i=0; $i < $diff; $i++) { 
						$days_td[$ug->guard_id][$i] = array(); 
						if ($request['report'] == 'divide') {

        // check if any shift ends in this time
							$shifts_ends_in_this_day = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '<', date('Y-m-d 00:00:00', $day_start))->where('temp_end','>', date('Y-m-d 00:00:00', $day_start))->where('guard_id', $ug->guard_id)->orderBy('start', 'ASC')->get();

							$shifts_start_in_this_day = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '>=', date('Y-m-d 00:00:00', $day_start))->where('start', '<=', date('Y-m-d 00:00:00', $day_end))->where('guard_id', $ug->guard_id)->orderBy('start', 'ASC')->get();

							$all_shifts = $shifts_ends_in_this_day->merge($shifts_start_in_this_day);
						}else{
							$all_shifts = DB::table('job_rosters')->where('site_id', $site_id->id)->where('start', '>=', date('Y-m-d 00:00:00', $day_start))->where('start', '<=', date('Y-m-d 00:00:00', $day_end))->orderBy('start', 'ASC')->where('guard_id', $ug->guard_id)->get();
						}

						foreach ($all_shifts as $all_shift) 
						{
							$guard = DB::table('guards')->where('id', $all_shift->guard_id)->first();
							if (!empty($guard) && !isset($guards_array[$guard->id])) {
								$guards_array[$guard->id] = array(
									'name' => $guard->first_name.' '.$guard->middle_name.' '.$guard->last_name, 
									'id' => $guard->id,
									'phone' => $guard->phone,
									'suburb' => $guard->suburb,
									'security_license_expiration' => '',
									'security_license_number' => '',
								);
							}
							$all_shift->temp_start = strtotime($all_shift->start); 
							$all_shift->temp_end = strtotime($all_shift->end);
							if ($request['report'] == 'divide') {
								if ($all_shift->temp_start < $day_start && $all_shift->temp_end > $day_start) {
									$all_shift->temp_start = $day_start + 100;
								}
								elseif ($all_shift->temp_start > $day_start && $all_shift->temp_end > $day_end) {
									$all_shift->temp_end = $day_end;
								}
							}
							$diff_hour = $all_shift->temp_end - $all_shift->temp_start;
							$hours = $diff_hour / ( 60 * 60 );
							$total_hours_count = explode('.', $hours);
							if (sizeof($total_hours_count) > 1 ) {
								$partial = '.'.$total_hours_count[1];
								if ($partial < 0.1) {
									$hours = $total_hours_count[0];
								}
								if ($partial < 0.27 && $partial > 0.1) {
									$hours = $total_hours_count[0].'.25';
								}
								if ($partial > 0.27 && $partial < 0.52) {
									$hours = $total_hours_count[0].'.5';
								}
								if ($partial > 0.52 && $partial < 0.77) {
									$hours = $total_hours_count[0].'.75';
								}
								if ($partial > 0.77 && $partial < 1) {
									$hours = $total_hours_count[0]+ 1;
								}
							}

							$days_td[$ug->guard_id][$i][] = array(
								'guard_name' => !empty($guard) ? $guard->name : $all_shift->moke_guard,
								'start' => date('H:i a', $all_shift->start),
								'end' => date('H:i a', $all_shift->end),
								'day_start' => date('m/d/Y H:i a', $day_start),
								'day_end' => date('m/d/Y H:i a', $day_end),
								'hours' => $hours,
								'training' => $all_shift->training
							);
						}
						$day_start = $day_end + 1;
						$day_end = $day_end + (60*60*24);
						if (count($days_td[$ug->guard_id][$i]) > $max_shifts_in_day) {
							$max_shifts_in_day = count($days_td[$ug->guard_id][$i]);
						}

					}
					$day_start = $start_str-1;
					$day_end = $start_str + (60*60*24)-1;
				}

// print_r('<pre>');
// print_r($days_td);
// exit();
				$total_hours[$site_id->id] = array(
					0 => 0,
					1 => 0,
					2 => 0,
					3 => 0,
					4 => 0,
					5 => 0,
					6 => 0
				);
				if (!empty($days_td)) {
					$pdf .= '
					<table style="width:100%;background-color:#CCC0DA;">
					<thead>
					<tr>
					<th style="background-color:#CCC0DA"></th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>

					<th style="text-align: center; font-size:22px; font-weight:bold; border:1px solid black; background-color:#CCC0DA;">
					<div style="text-align: center; font-size:18px;">'.$site->site_name.' - '. htmlspecialchars($site->site_description).'</div>
					</th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>
					<th style="width:200px;background-color:#CCC0DA;border:1px solid black;"></th>

					</tr>
					</thead>
					</table>';
					$pdf .='<table style="width:100%;">
					<thead>
					<tr>
					<th style="border:1px solid #000000;width: 20px;font-size:15px;background-color:#F2DCDB;">Staff\'s
					Name
					</th>';
					for ($i=$start_str; $i < $end_str;) { 
						$pdf .= '<th style="border:1px solid #000000;width: 20px;font-size:15px;background-color:#F2DCDB;">'.date('D M d', $i).'</th>';
						$i = $i + (60*60 *24);
					}
					$pdf .='<th style="border:1px solid #000000;width: 20px; font-size:15px; background-color:#F2DCDB;">
					Total Hrs | Wages
					</th>
					</tr></thead>
					<tbody>';
				}
				$c = 0;
				foreach($days_td as $key => $value){
					$background_color = '#DCE6F1';
					if ($c % 2 == 0) {
						$background_color = '';
					}
					for ($i=0; $i < $max_shifts_in_day; $i++) { 

						$pdf .= '<tr>';
						$pdf .= '<td style="border:1px solid #000000; background-color:'.$background_color.'; width:200px;">'.(isset($guards_array[$key]['name']) ? $guards_array[$key]['name'] : 'N/A').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][0][$i])) ? $days_td[$key][0][$i]['start']. ' - '. $days_td[$key][0][$i]['end'].'('.$days_td[$key][0][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][0][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][1][$i])) ? $days_td[$key][1][$i]['start']. ' - '. $days_td[$key][1][$i]['end'].'('.$days_td[$key][1][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][1][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][2][$i])) ? $days_td[$key][2][$i]['start']. ' - '. $days_td[$key][2][$i]['end'].'('.$days_td[$key][2][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][2][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][3][$i])) ? $days_td[$key][3][$i]['start']. ' - '. $days_td[$key][3][$i]['end'].'('.$days_td[$key][3][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][3][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][4][$i])) ? $days_td[$key][4][$i]['start']. ' - '. $days_td[$key][4][$i]['end'].'('.$days_td[$key][4][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][4][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][5][$i])) ? $days_td[$key][5][$i]['start']. ' - '. $days_td[$key][5][$i]['end'].'('.$days_td[$key][5][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][5][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';">'.((isset($days_td[$key][6][$i])) ? $days_td[$key][6][$i]['start']. ' - '. $days_td[$key][6][$i]['end'].'('.$days_td[$key][6][$i]['hours'].'h)'.(($request['report'] == 'normal' && $days_td[$key][6][$i]['training'] == 1) ? ' (T)' : '') : '').'</td>';
						$pdf .= '<td style="border:1px solid #000000;width:200px;background-color:'.$background_color.';"></td>';
						$total_hours[$site_id->id][0] = $total_hours[$site_id->id][0] + (isset($days_td[$key][0][$i]) ? $days_td[$key][0][$i]['hours'] : 0);
						$total_hours[$site_id->id][1] = $total_hours[$site_id->id][1] + (isset($days_td[$key][1][$i]) ? $days_td[$key][1][$i]['hours'] : 0); 
						$total_hours[$site_id->id][2] = $total_hours[$site_id->id][2] + (isset($days_td[$key][2][$i]) ? $days_td[$key][2][$i]['hours'] : 0); 
						$total_hours[$site_id->id][3] = $total_hours[$site_id->id][3] + (isset($days_td[$key][3][$i]) ? $days_td[$key][3][$i]['hours'] : 0); 
						$total_hours[$site_id->id][4] = $total_hours[$site_id->id][4] + (isset($days_td[$key][4][$i]) ? $days_td[$key][4][$i]['hours'] : 0); 
						$total_hours[$site_id->id][5] = $total_hours[$site_id->id][5] + (isset($days_td[$key][5][$i]) ? $days_td[$key][5][$i]['hours'] : 0); 
						$total_hours[$site_id->id][6] = $total_hours[$site_id->id][6] + (isset($days_td[$key][6][$i]) ? $days_td[$key][6][$i]['hours'] : 0); 

						$pdf .= '</tr>';
					}
					$c++;
				}
// dd($total_hours);
// exit;
				if (($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6]) > 0) {
					$pdf .= '<tr>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">Sub Total Hrs | Wages</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][0].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][1].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][2].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][3].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][4].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][5].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.$total_hours[$site_id->id][6].'</td>';
					$pdf .= '<td style="border:1px solid #000000;background-color:#E8E8B6;">'.($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6]).'</td>';
					$pdf .= '</tr>';
					$pdf .='</tbody></table>';

					$total_hours[0] = array(
						0 => $total_hours[0][0] + $total_hours[$site_id->id][0],
						1 => $total_hours[0][1] + $total_hours[$site_id->id][1],
						2 => $total_hours[0][2] + $total_hours[$site_id->id][2],
						3 => $total_hours[0][3] + $total_hours[$site_id->id][3],
						4 => $total_hours[0][4] + $total_hours[$site_id->id][4],
						5 => $total_hours[0][5] + $total_hours[$site_id->id][5],
						6 => $total_hours[0][6] + $total_hours[$site_id->id][6],
						7 => $total_hours[0][7] + ($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6])
					);
				}

// if (count($sites) > 1 && ($total_hours[$site_id->id][0] + $total_hours[$site_id->id][1] + $total_hours[$site_id->id][2]+ $total_hours[$site_id->id][3]+ $total_hours[$site_id->id][4]+ $total_hours[$site_id->id][5]+ $total_hours[$site_id->id][6]) > 0) {

//   $pdf .= '<tr>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '<td style="border:1px solid #000000;height:25px;background-color:#EDEDED;"></td>';
//   $pdf .= '</tr>';
//   $counter++;
// }

			}
// $pdf .= '<tr>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '<td style="height:25px;"></td>';
//   $pdf .= '</tr>';

// $pdf .= '<tr>';
// $pdf .= '<td style="border:1px solid #000000;">Total Hrs | Wages</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][0].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][1].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][2].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][3].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][4].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][5].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][6].'</td>';
// $pdf .= '<td style="border:1px solid #000000;">'.$total_hours[0][7].'</td>';
// $pdf .= '</tr>';
// $pdf .= '<tr>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '<td style="border:1px solid #000000;"></td>';
// $pdf .= '</tr>';

// $pdf .='</tbody></table>';
			if (!empty($guards_array)) {

				$pdf .='<table style="width:100%;"><tbody>
				<tr>
				<td style="border:1px solid #000000; background-color:#D8E4BC;">
				<div style="text-align: center;" class="title">Staff Details</div>
				</td>
				</tr></tbody></table>
				<table style="width:100%;">
				<tr>
				<th style="border:1px solid #000000;background-color:#C5D9F1;">
				Staff name
				</th>
				<th style="border:1px solid #000000;background-color:#C5D9F1;">
				Staff Mob
				</th>
				<th style="border:1px solid #000000;background-color:#C5D9F1;">
				Suburb
				</th>
				<th style="border:1px solid #000000;background-color:#C5D9F1;">
				Security License
				</th>
				<th style="border:1px solid #000000;background-color:#C5D9F1;">
				Security License Expiry
				</th>
				</tr><tbody>
				';
				$c = 0;
				foreach ($guards_array as $key => $guard) {
					$background_color = '#DCE6F1';
					if ($c % 2 == 0) {
						$background_color = '';
					}
					$pdf .='<tr>
					<td style="border:1px solid #000000;background-color:'.$background_color.';">'.$guard['name'].'</td>
					<td style="border:1px solid #000000;background-color:'.$background_color.';">'.$guard['phone'].'</td>
					<td style="border:1px solid #000000;background-color:'.$background_color.';">'.$guard['suburb'].'</td>
					<td style="border:1px solid #000000;background-color:'.$background_color.';">'.$guard['security_license_number'].'</td>
					<td style="border:1px solid #000000;background-color:'.$background_color.';">'.$guard['security_license_expiration'].'</td>
					</tr>';
					$c++;
				}
				$pdf .= '</tbody></table>';
// if ($key <= count($customers)) {
//     $pdf .= '<div class="page-break"></div>';
// }
			}
		}
// $pdf .= '</div>
// </div></body></html>';
// $pdf_data = $pdf;
// return View('admin/report/excel',compact('pdf_data'));
// echo $pdf;
// exit();
		return $pdf;
}

	public function generateSigninoutReport(Request $request)
	{
		if($request->type == 'preview'){
			return response()->json([
				'success' => true,
				'data' => $this->signin_out_report($request)
			]);
		}else{
			$filename = time().'_signinout_report.xlsx';  
			Excel::store(new SigninoutReport, 'excel/roster/'.$filename, 'excels');
			return response()->json(['success' =>  true, 'message' => 'Signinout Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/roster/'.$filename]);  
			// return Excel::download(new SigninoutReport, 'signinout_report.xlsx'); 
		}
	}

	function signin_out_report($request)
	{
		if (isset($request['date'])) {
			$date = $request['date'];
			$date = explode(' - ', $date);
            $from = strtotime(trim(str_replace('-', '/', $date[0])));
            $to = strtotime(trim(str_replace('-', '/', $date[1])));
		}else{
			$to = time();
			$from = time() - (60*60*24*30);
		}
		$from = date('Y-m-d 00:00', $from);
		$to = date('Y-m-d 23:59', $to);
// 		dd(JobRoster::whereBetween('job_rosters.start', [$from, $to])->get());
		$query = JobRoster::with(['greenCall', 'WelfareCall', 'activity', 'Guards', 'Sites'])
		->whereBetween('job_rosters.start', [$from, $to]);
		if (isset($request['sites'])) {
			$query->whereIn('site_id', $request['sites']);
		}
		if (isset($request['customer_id'])) {
			if (is_array($request['customer_id'])) {
				$customer_id = $request['customer_id'];
			}else{
				$customer_id = explode(',', $request['customer_id']);
			}
// 			$query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
// 			$query->whereIn('sites.customer_id', $customer_id);
		}
		$query->where('guard_id', '>', 0);
		$query->orderBy('job_rosters.start', 'asc');
		
		return $query->get();
	} 

	private function getShiftHours($start, $end, $siteID = null, $continuation = false, $public_holiday = null, $ph_duration = null) 
{
    $actual_start = $start;
    $actual_end = $end;
    $day_start = Carbon::parse($start)->format('l');
    $day_end = Carbon::parse($end)->format('l');

    $start = strtotime($start);
    $end = strtotime($end);

    $diff = $end - $start;
    $hours = round($diff / ( 60 * 60 ), 2);
    $hoursCond = round($diff / ( 60 * 60 ), 2);

    $morning_start = 6;
    $morning_end = 18;

    $night_start = 18;
    $night_end = 6;

    $shift_start = $this->convert_into_fraction($start);
    $shift_end = $this->convert_into_fraction($end);
    // return [$shift_start,$shift_end];
    if ($shift_end < $shift_start) {
        $diff_new = $shift_end + 24 - $shift_start;
        if ($diff > $diff_new) {
               $hours = $diff_new;
           }   
    }
    // saturday calcultions
    $saturday_start = 0;
    $saturday_end = 0;
    $total_saturday_hours = 0;

    $sunday_start = 0;
    $sunday_end = 0;
    $total_sunday_hours = 0;

    $total_ph_hours = 0;
    $ph_start = 0;
    $ph_end = 0;

    // publid holiday calculation start here
    $start_in_public_holiday = false;
    $end_in_public_holiday = false;
    if ($siteID != null) {
        $site_state = DB::table('sites')->where('id', $siteID)->select('state')->first();

        $states_array = array(
            'Victoria' => 'vic',
            'New South Wales' => 'nsw',
            'NSW' => 'nsw',
            'Queensland' => 'qld',
            'Tasmania' => 'tas',
            'Western Australia' => 'wa',
            'South Australia' => 'sa',
            'ACT' => 'act'
        );

        if ($site_state && !empty($site_state->state) && isset($states_array[$site_state->state])) {
            $state = $states_array[$site_state->state];
        } else {
            $state = 'vic';
        }
    } else {
        $state = 'vic';
    }  
    $public_holiday_start = DB::table('public_holidays')->where('date', date('Ymd', $start))->where('state', $state)->first();
    if ($public_holiday != null && $public_holiday == 1) {
        $start_in_public_holiday = true;
    }elseif (!empty($public_holiday_start)) {
        $start_in_public_holiday = true;
    }

    $public_holiday_end = DB::table('public_holidays')->where('date', date('Ymd', $end))->where('state', $state)->first();
    if (!empty($public_holiday_end)) {
        $end_in_public_holiday = true;
    }elseif($public_holiday != null && $public_holiday == 1 && $ph_duration == 1){
        $end_in_public_holiday = true;
    }

    if ($start_in_public_holiday && $end_in_public_holiday) {
        $total_ph_hours = $hours;
        $hours = 0;
        $ph_start = $shift_start;
        $ph_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        // echo 'whole day in PH - ';

    }elseif($start_in_public_holiday && !$end_in_public_holiday)
    {
        $ph_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $ph_end - $start;
        $total_ph_hours = round($diff / ( 60 * 60 ), 2);
        $ph_start = $this->convert_into_fraction($start);
        $ph_end = $this->convert_into_fraction($ph_end);
        $start = $public_holiday_start ? strtotime($public_holiday_start->date) + (60*60*24) : $start + (60*60*24) ;
        $day_start = Carbon::parse(date('m/d/Y', $end))->format('l');
        $hours = $hours - $total_ph_hours;
        $shift_start = 0;
        // echo 'Start in PH - '.$day_start;
    }elseif(!$start_in_public_holiday && $end_in_public_holiday){

        $ph_start_ts = strtotime(date('m/d/Y 00:00:00', $end));
        $diff = $end - $ph_start_ts;
        $total_ph_hours = round($diff / 3600, 2);
        $ph_start = $this->convert_into_fraction($ph_start_ts);
        $ph_end   = $this->convert_into_fraction($end);
        $end = $ph_start_ts;

        $shift_end = $this->convert_into_fraction($end);
        $hours = $hours - $total_ph_hours;
    }
    
  // if ($total_ph_hours == 0) {

    if ($day_start == 'Saturday' && $day_end == 'Saturday') {
        $total_saturday_hours = $hours;
        $saturday_start = $shift_start;
        $saturday_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        $hours = 0;
    }elseif($day_start == 'Saturday' && $day_end != 'Saturday')
    {
        $sat_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $sat_end - $start;
        $total_saturday_hours = round($diff / ( 60 * 60 ), 2);
        $saturday_start = $shift_start;
        $saturday_end = $this->convert_into_fraction($sat_end);
        $shift_start = 0;
        $shift_end = 0;
        $hours = $hours - $total_saturday_hours;
    }elseif($day_start != 'Saturday' && $day_end == 'Saturday')
    {
        $sat_start = strtotime(date('m/d/Y 00:00:00', $end));
        $diff = $end - $sat_start;
        $total_saturday_hours = round($diff / ( 60 * 60 ), 2);
        $saturday_start = $this->convert_into_fraction($sat_start);
        $saturday_end = $shift_end;
        $shift_end = 24;
        $hours = $hours - $total_saturday_hours;
    }
    // sunday_calcultaon
    
    if ($day_start == 'Sunday' && $day_end == 'Sunday') {
        $total_sunday_hours = $hours;
        $sunday_start = $shift_start;
        $sunday_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        $hours = 0;
    }elseif($day_start == 'Sunday' && $day_end != 'Sunday')
    {
        $sun_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $sun_end - $start;
        $total_sunday_hours = round($diff / ( 60 * 60 ), 2);
        $sunday_start = $shift_start;
        $sunday_end = $this->convert_into_fraction($sun_end);

        $shift_start = 0;
        $hours = $hours-$total_sunday_hours;
    }elseif($day_start != 'Sunday' && $day_end == 'Sunday')
    {
        $sun_start = strtotime(date('m/d/Y 00:00:00', $end));
        // $diff = $end - $sun_start;
        // $total_sunday_hours = round($diff / ( 60 * 60 ), 2);
        $sunday_start = $this->convert_into_fraction($sun_start);
        $sunday_end = $this->convert_into_fraction($end);
        $total_sunday_hours = $sunday_end - $sunday_start;
        $shift_end = 24;
        $shift_start = 24;
        $hours = $hours - $total_sunday_hours;
    }
  // }
    if ($start_in_public_holiday && $end_in_public_holiday) {
        $shift_start = 0;
        $shift_end = 0;
        $saturday_start = 0;
        $saturday_end = 0;
        $sunday_start = 0;
        $sunday_end = 0;
        $total_sunday_hours = 0;
        $total_saturday_hours = 0;
    }

    if ($shift_end < $shift_start && $shift_end < 6 && $shift_end >= 1) {
        $shift_end += 24; 
    }

    $morning = $this->calculateHoursMorning($shift_start, $shift_end, $morning_start, $morning_end, $actual_start, $actual_end);
    if($morning > 12){
        $morning = $morning - 12;
    }

    $saturday_morning = round($this->calculateHoursMorning($saturday_start, $saturday_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    $sunday_morning = round($this->calculateHoursMorning($sunday_start, $sunday_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    $ph_morning = round($this->calculateHoursMorning($ph_start, $ph_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    if ($morning < 0) {
        $morning = 0;
    }
    if ($saturday_morning < 0) {
        $saturday_morning = 0;
    }
    if ($sunday_morning < 0) {
        $sunday_morning = 0;
    }

    return [
        'morning' =>  $morning,
        'night' => round(((($hours - $morning) < 0) ? 0 : ($hours - $morning)), 2),
        'saturday_morning' => $saturday_morning,
        'saturday_night' => round(((($total_saturday_hours - $saturday_morning) < 0) ? 0 : ($total_saturday_hours - $saturday_morning)), 2),
        'sunday_morning' => $sunday_morning,
        'sunday_night' => round(((($total_sunday_hours - $sunday_morning) < 0) ? 0 : ($total_sunday_hours - $sunday_morning)), 2),
        'ph_morning' => $ph_morning,
        'ph_night' => round(((($total_ph_hours - $ph_morning) < 0) ? 0 : ($total_ph_hours - $ph_morning)), 2),
    ];
}

    function calculateHoursMorning($shift_start, $shift_end, $start, $end)
    {
       $shift_hours = 0;
       $shift_end1 = $shift_end;
       if ($shift_end < $shift_start || $shift_end == 00) {
        $shift_end1 = $shift_end + 24;
    }
    $shift_hours = $shift_end1 - $shift_start;
    //     if ($shift_end < $shift_start) {
    //     $shift_end = $shift_end - 24;
    // }
    // echo $shift_start.' -- '.$shift_end;
    //     echo '<br>';
    //     echo $start.' -- '.$end;
    //     echo '<br>';
        // echo $shift_end1;

        // exit();

    if ($shift_hours > 12) {

        // echo $shift_hours;
            


        if($shift_start <= $start && $shift_end > $end){
            // shift start in night and end in night
            return 12;
        }elseif($shift_start >= $start && $shift_start <= $end && $shift_end >= $start && $shift_end < $end){
            // shift start in day and end in day
         return ($end - $shift_start) + ($shift_end - $start);
     }elseif($shift_start < $start && $shift_end > $start && $shift_end <= $end)
     {
                // shift start in night and end in day
        return $shift_end - 6;
    }
    elseif($shift_start > $start && $shift_start > $end && $shift_end <=24 && $shift_end > $start && $shift_end <= $end)
    {
                // shift start in night but in between 18:00 and 24:00 and end in day
        return $shift_end - 6;
    }
    elseif($shift_start >= $start && $shift_start <= $end && $shift_end > $end)
    {
                // shift start in day and end in night
        return 18 - $shift_start;
    }
           // elseif($shift_start > $start && $shift_start < $end && $shift_end > $start){
           //     return ($end - $shift_start) + ($shift_end - $start);
           // }
    else{
        return 0;
    }
}else{
// echo 'i am here';
    if ($shift_end <= 6 && $shift_end < $shift_start) {
        $shift_end += 24;
    }
   if (($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)) {
     return $shift_end - $shift_start;
 }elseif(($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end > $end))
 {
    $shift_end = $end;
    return $shift_end - $shift_start;
}elseif(($shift_start > $start && $shift_start > $end) && ($shift_end > $start && $shift_end <= $end)){
    $shift_start = $start;
    return $shift_end - $shift_start;
}elseif(($shift_start < $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)){
    $shift_start = $start;
    return $shift_end - $shift_start;
}elseif($shift_start < $start && $shift_end > $end){
    return $end - $start;
}elseif($shift_start >= $end && $shift_end > $start && $shift_end < $end)
{
        // shift start in night in gone into day
        // echo 'Here';
    return $shift_end - $start;
}
elseif($shift_start > $start && $shift_end < $end && $shift_end > $start){
    return $end - $shift_start;
}elseif(true)
{

}   
else{
    return 0;
}
}



           //  if ($shift_start > $end && $shift_end > $end) {
           //      return 0;
           //  }elseif ($shift_start > $start && $shift_start > $end) {
           //      $shift_start = $start;
           // }

           // if ($shift_end > $start && $shift_end > $end) {
           //  $shift_end = $end;
           // }


}
function convert_into_fraction($time)
{
    return date('H', $time) + (date('i', $time) / 60);
}

}
