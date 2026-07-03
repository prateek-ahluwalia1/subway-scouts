<table>
 <thead>
  <tr>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Date</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Wilson ID</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Certis ID</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Staff First Name</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Staff Last Name</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Customer Name</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Level</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Start</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Finish</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Hours</th>
    @if($report == 'divide')
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Day</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Night</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Saturday Day</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Saturday Night</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sunday Day</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sunday Night</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">PH Day</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">PH Night</th>
    @endif
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Status</th>
   
 </tr>
</thead>
<tbody>
<?php 
	$guard_array = [];
	$guard_array_tr = '';
?>

@foreach($data as $d)
                   
@php
	$d->id1 = 'N/A';
	$d->id2 = 'N/A';
	$ids = DB::table('guard_external_ids')->where('guard_id', $d->guard_id)->get();
	foreach ($ids as $id) {
		if(preg_match('/AMG/i', $id->external_id)){
			$d->id1 = $id->external_id;
		} 
		if (!preg_match('/AMG/i', $id->external_id) && $id->external_id > 0) {
			$d->id2 = $id->external_id;
		}
	}
@endphp
<tr>
	<td style="border:1px solid #000000;width: 100px;">{{date('d/m/Y', strtotime($d->start))}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->id1}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{($d->id2)}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->first_name}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->last_name}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->customer_name}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->site_name}} - {{$d->site_description}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->level}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{date('H:i', strtotime($d->start))}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{date('H:i', strtotime($d->end))}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->hours}}</td>
	@if($report == 'divide')
    <td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['morning'] ?? 0 }}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['night']}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['saturday_morning']}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['saturday_night']}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['sunday_morning']}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['sunday_night']}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['ph_morning']}}</td>
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_hours['ph_night']}}</td>
    @endif
	<td style="border:1px solid #000000;width: 100px;">{{$d->job_status}}</td>
</tr>
@if(!isset($guard_array[$d->guard_id]))

<?php 
	$guard_array[$d->guard_id] = $d->guard_id;
	$guard_array_tr .= '<tr>
	<td style="border:1px solid #000000;width: 100px;">'.$d->first_name.' '.$d->last_name.'</td>
	<td style="border:1px solid #000000;width: 100px;">'.$d->phone.'</td>
	<td style="border:1px solid #000000;width: 100px;">'.$d->email.'</td>
	<td style="border:1px solid #000000;width: 100px;"></td>
	<td style="border:1px solid #000000;width: 100px;"></td>
	<td style="border:1px solid #000000;width: 100px;"></td>
	<td style="border:1px solid #000000;width: 100px;"></td>
	<td style="border:1px solid #000000;width: 100px;"></td>
</tr>'
?>
@endif
@endforeach
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;">Guard Details</td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
	<td style="border:1px solid #000000; font-size: 14px;width: 100px;"></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td style="border:1px solid #000000; width: 100px;">Guard Name</td>
	<td style="border:1px solid #000000; width: 100px;">Guard Mob</td>
	<td style="border:1px solid #000000; width: 100px;">Email</td>
	<td style="border:1px solid #000000; width: 100px;"></td>
	<td style="border:1px solid #000000; width: 100px;"></td>
	<td style="border:1px solid #000000; width: 100px;"></td>
	<td style="border:1px solid #000000; width: 100px;"></td>
	<td style="border:1px solid #000000; width: 100px;"></td>
</tr>
{{!! $guard_array_tr !!}}
</tbody>
</table>