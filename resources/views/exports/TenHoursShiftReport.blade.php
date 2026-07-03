<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;"></th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">AMG MS 0-10 Hrs Report</th>
 </tr>
 <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;"></th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Week {{$data['date']}}</th>
 </tr>
</thead>
</table>
<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Employee Name</th>
    <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Wilson ID</th>
    <!-- <th style="text-align:center; background-color: #00B050; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Certis ID</th> -->
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;"> Mon-Fri 10 Hrs </th>
 </tr>
</thead>
<tbody>

  ?>
 @foreach($data['data'] as $key => $val)
 @if($val->total > 0 && $val->total <= 10)
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val->guard_name}}</td>
  <td style="border:1px solid #000000;width: 20px;">{{$val->id1}}</td>
  <!-- <td style="border:1px solid #000000;width: 20px;">{{($val->id2)}}</td> -->
  <td style="text-align:center;border:1px solid #000000;">{{$val->total}}</td>
</tr>
@endif
@endforeach


</tbody>

</table>