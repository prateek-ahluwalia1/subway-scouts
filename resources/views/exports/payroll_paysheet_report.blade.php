<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Employee ID</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Date</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Category</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Tracking code 1</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Tracking code 2</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Units</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Rates</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Comments</th>
   
 </tr>
</thead>
<tbody>

  ?>
 @foreach($data as $key => $val)
 <?php 
 $val['guard_name'] = $val['guard_name'] != '' ? $val['guard_name'] : 'N/A';
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
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['night_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['saturday_day_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['saturday_night_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['sunday_day_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['sunday_night_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if ($val['ph_day_hours'] > 0 || $val['ph_night_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{($val['ph_day_hours'] + $val['ph_night_hours'])}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>
  <?php
  if (false && $val['ph_night_hours'] > 0) {
?>
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['external_id']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['ph_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
</tr>
<?php } ?>


@endforeach


</tbody>

</table>