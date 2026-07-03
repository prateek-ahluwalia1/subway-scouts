<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;"></th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">AMG MS 40-48 Hrs Report</th>
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
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Employee ID</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Employee Name</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;"> Mon-Sun 40-48 Hrs </th>
 </tr>
</thead>
<tbody>

  ?>
 @foreach($data['data'] as $key => $val)
 @if($val->total >= 40)
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val->external_id}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val->guard_name}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val->total}}</td>
</tr>
@endif
@endforeach


</tbody>

</table>