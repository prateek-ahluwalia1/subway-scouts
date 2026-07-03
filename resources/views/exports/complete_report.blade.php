<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Employee ID</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Start Date</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">End Date</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Name</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Status</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Start Time</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">End Time</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Client</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Category</th>
      <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Category New</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Tracking code 1</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Tracking code 2</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Units</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Rates</th>
      <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Rates New</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Comments</th>
   
 </tr>
</thead>
<tbody>
@php
$grandHoursSum = 0;
@endphp
  ?>
 @foreach($data as $key => $val)
 <?php 
//  $val['guard_name'] = $val['guard_name'] != '' ? $val['guard_name'] : 'N/A';
    ?>
  <!--   <tr>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  
</tr> -->
    <?php
  if ($val['day_hours'] > 0) {
    $grandHoursSum += $val['day_hours'];
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Day</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} Day</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['day_rate']}}</td>
    <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['day_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['night_hours'] > 0) {
    $grandHoursSum += $val['night_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Night</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} Night</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['night_rate']}}</td>
    <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['night_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['saturday_day_hours'] > 0) {
    $grandHoursSum += $val['saturday_day_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Saturday Day</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} Saturday Day</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_day_rate']}}</td>
    <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_day_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['saturday_night_hours'] > 0) {
    $grandHoursSum += $val['saturday_night_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Saturday Night</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} Saturday Night</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_night_rate']}}</td>
      <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_night_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['sunday_day_hours'] > 0) {
    $grandHoursSum += $val['sunday_day_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Sunday Day</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} Sunday Day</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_day_rate']}}</td>
    <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_day_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['sunday_night_hours'] > 0) {
    $grandHoursSum += $val['sunday_night_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Sunday Night</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} Sunday Night</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_night_rate']}}</td>
    <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_night_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['ph_day_hours'] > 0 || $val['ph_night_hours'] > 0) {
    $grandHoursSum += $val['ph_day_hours'] + $val['ph_night_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} PH Day</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} PH Day</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{($val['ph_day_hours'] + $val['ph_night_hours'])}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_day_rate']}}</td>
    <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_day_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if (false && $val['ph_night_hours'] > 0) {
    $grandHoursSum += $val['ph_night_hours'];

?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} PH Night</td>
    <td style="text-align:center;border:1px solid #000000;">Award {{$val['payrate_name_new']}} PH Night</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['ph_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_night_rate']}}</td>
      <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_night_rate_new']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>


@endforeach

<tr>
  <td style="text-align:center;border:1px solid #000000;"><h2>Grand Sum</h2></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"><h2>{{$grandHoursSum}}</h2></td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
</tr>
</tbody>

</table>