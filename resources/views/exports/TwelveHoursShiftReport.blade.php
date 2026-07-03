<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;"></th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">AMG Shift > 12 Hrs Report</th>
 </tr>
 <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;"></th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Week {{$data['date']}}</th>
 </tr>
</thead>
</table>
<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Site Name</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Site Description</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Guard Name</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">ID</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Start</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">End</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;"> Total </th>
 </tr>
</thead>
<tbody>

  ?>
 @foreach($data['data'] as $key => $val)
 
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val->site_name}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val->site_description}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val->guard_name}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val->external_id}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{date('d/m/Y H:i', strtotime($val->start))}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{date('d/m/Y H:i', strtotime($val->end))}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val->hours}}</td>
</tr>

@endforeach


</tbody>

</table>