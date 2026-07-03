<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;"></th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">AMG MS >36-40 Hours Report</th>
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
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Employee ID</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Employee Name</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;"> Mon-Sat </th>
 </tr>
</thead>
<tbody>

  ?>
@foreach($data['data'] as $key => $val)
  @if($val->total > 36 && $val->total <= 40)
  <tr>
    <td style="text-align:center;border:1px solid #000000; font-size: 14px;width: 100px;">{{$val->external_id}}</td>
    <td style="text-align:center;border:1px solid #000000; font-size: 14px;width: 100px;">{{$val->guard_name}}</td>
    <td style="text-align:center;border:1px solid #000000; font-size: 14px;width: 100px;">{{$val->total}}</td>
  </tr>
  @endif
@endforeach


</tbody>

</table>
