<table>
 <thead>
  <tr>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Employee ID</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Start Date</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">End Date</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Name</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Status</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Start Time</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">End Time</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Client</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Site</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Category</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Tracking code 1</th>
    <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Tracking code 2</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Units</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Rates</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Comments</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">EBA Total</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Award Total</th>
   <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Diff</th>
   
 </tr>
</thead>
<tbody>

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
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_date_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['position']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['payrate_name']}} Day</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  
  <td style="text-align:center;border:1px solid #000000;">${{number_format(($val['day_hours'] * $val['day_rate']), 2)}}</td>
  <?php
    if ($val['2x'] == true) {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format(2* ($val['day_hours'] * $val['award_rate']['award_day_rate']), 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(2 * ($val['day_hours'] * $val['award_rate']['award_day_rate']) - ($val['day_hours'] * $val['day_rate']) < 0 ? 'red' : 'green')}};">
    ${{number_format(2 * ($val['day_hours'] * $val['award_rate']['award_day_rate']) - ($val['day_hours'] * $val['day_rate']), 2)}}</td>
  <?php }
    else {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format($val['day_hours'] * $val['award_rate']['award_day_rate'], 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['day_hours'] * $val['award_rate']['award_day_rate']) - ($val['day_hours'] * $val['day_rate']) < 0 ? 'red' : 'green')}};">
    ${{number_format(($val['day_hours'] * $val['award_rate']['award_day_rate']) - ($val['day_hours'] * $val['day_rate']), 2)}}</td>
  <?php } ?>
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['day_hours'] * $val['day_rate']) - $val['day_hours'] * $val['award_rate']['award_day_rate'] < 0 ? 'red' : 'green')}};">
  ${{number_format((($val['day_hours'] * $val['day_rate']) - $val['day_hours'] * $val['award_rate']['award_day_rate']), 2)}}</td> -->

</tr>
<?php } ?>
  <?php
  if ($val['night_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format(($val['night_hours'] * $val['night_rate']), 2)}}</td>
  <?php
    if ($val['2x'] == true) {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format(2 * ($val['night_hours'] * $val['award_rate']['award_night_rate']), 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{((2 *($val['night_hours'] * $val['award_rate']['award_night_rate'])- ($val['night_hours'] * $val['night_rate'])) < 0 ? 'red' : 'green')}}">
    ${{number_format((2 *($val['night_hours'] * $val['award_rate']['award_night_rate']) - ($val['night_hours'] * $val['night_rate'])), 2)}}</td>
  <?php }
    else {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format($val['night_hours'] * $val['award_rate']['award_night_rate'], 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['night_hours'] * $val['award_rate']['award_night_rate'])- ($val['night_hours'] * $val['night_rate']) < 0 ? 'red' : 'green')}};">
    ${{number_format((($val['night_hours'] * $val['award_rate']['award_night_rate']) - ($val['night_hours'] * $val['night_rate'])), 2)}}</td>
  <?php } ?>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['night_hours'] * $val['award_rate']['award_day_rate'], 2)}}</td> -->
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: {{($val['night_hours'] * $val['night_rate']) - $val['night_hours'] * $val['award_rate']['award_night_rate'] < 0 ? 'red' : 'green'}};">
  ${{number_format((($val['night_hours'] * $val['night_rate']) - $val['night_hours'] * $val['award_rate']['award_night_rate']), 2)}}</td> -->
</tr>
<?php } ?>
  <?php
  if ($val['saturday_day_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['saturday_day_hours'] * $val['saturday_day_rate'], 2)}}</td>
  <?php
    if ($val['2x'] == true) {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format(2 *($val['saturday_day_hours'] * $val['award_rate']['award_day_rate']), 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(2 *($val['saturday_day_hours'] * $val['award_rate']['award_day_rate']) - ($val['saturday_day_hours'] * $val['saturday_day_rate']) < 0 ? 'red' : 'green')}};">
    ${{number_format(2 *($val['saturday_day_hours'] * $val['award_rate']['award_day_rate']) - ($val['saturday_day_hours'] * $val['saturday_day_rate']), 2)}}</td>
  <?php }
    else {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format($val['saturday_day_hours'] * $val['award_rate']['award_saturday_day_rate'], 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['saturday_day_hours'] * $val['award_rate']['award_saturday_day_rate']) - ($val['saturday_day_hours'] * $val['saturday_day_rate']) < 0 ? 'red' : 'green')}};">
    ${{number_format(($val['saturday_day_hours'] * $val['award_rate']['award_saturday_day_rate']) - ($val['saturday_day_hours'] * $val['saturday_day_rate']), 2)}}</td>
  <?php } ?>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['saturday_day_hours'] * $val['award_rate']['award_saturday_day_rate'], 2)}}</td> -->
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: {{($val['saturday_day_hours'] * $val['saturday_day_rate'] - $val['saturday_day_hours'] * $val['award_rate']['award_saturday_day_rate'] < 0 ? 'red' : 'green')}};">
  ${{number_format(($val['saturday_day_hours'] * $val['saturday_day_rate'] - $val['saturday_day_hours'] * $val['award_rate']['award_saturday_day_rate']), 2)}}</td> -->
</tr>
<?php } ?>
  <?php
  if ($val['saturday_night_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['saturday_night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['saturday_night_hours'] * $val['saturday_night_rate'], 2)}}</td>
  <?php
    if ($val['2x'] == true) {
  ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format(2 *($val['saturday_night_hours'] * $val['award_rate']['award_day_rate']), 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(2 *($val['saturday_night_hours'] * $val['award_rate']['award_day_rate']) - ($val['saturday_night_hours'] * $val['saturday_night_rate']) < 0 ? 'red' : 'green')}};">
      ${{number_format(2 *($val['saturday_night_hours'] * $val['award_rate']['award_day_rate']) - ($val['saturday_night_hours'] * $val['saturday_night_rate']), 2)}}</td>
    <?php }
      else {
    ?>
    <td style="text-align:center;border:1px solid #000000;">${{number_format($val['saturday_night_hours'] * $val['award_rate']['award_saturday_night_rate'], 2)}}</td>
    <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['saturday_night_hours'] * $val['award_rate']['award_saturday_night_rate']) - ($val['saturday_night_hours'] * $val['saturday_night_rate']) < 0 ? 'red' : 'green')}};">
      ${{number_format(($val['saturday_night_hours'] * $val['award_rate']['award_saturday_night_rate']) - ($val['saturday_night_hours'] * $val['saturday_night_rate']), 2)}}</td>
    <?php } ?>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['saturday_night_hours'] * $val['award_rate']['award_saturday_night_rate'], 2)}}</td> -->
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: {{($val['saturday_night_hours'] * $val['saturday_night_rate'] - $val['saturday_night_hours'] * $val['award_rate']['award_saturday_night_rate'] < 0 ? 'red' : 'green')}};">
  ${{number_format(($val['saturday_night_hours'] * $val['saturday_night_rate'] - $val['saturday_night_hours'] * $val['award_rate']['award_saturday_night_rate']), 2)}}</td> -->
</tr>
<?php } ?>
  <?php
  if ($val['sunday_day_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;">Over Time Sunday Day</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['sunday_day_hours'] * $val['sunday_day_rate'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format(($val['sunday_day_hours'] * $val['award_rate']['award_sunday_day_rate']), 2)}}</td>
  <td style="text-align:center;border:1px solid #000000; background-color: {{(($val['sunday_day_hours'] * $val['award_rate']['award_sunday_day_rate']) - ($val['sunday_day_hours'] * $val['sunday_day_rate']) < 0 ? 'red' : 'green')}};">
  ${{number_format((($val['sunday_day_hours'] * $val['award_rate']['award_sunday_day_rate']) - ($val['sunday_day_hours'] * $val['sunday_day_rate'])), 2)}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['sunday_day_hours'] * $val['award_rate']['award_sunday_day_rate'], 2)}}</td> -->
  <!-- <td style="text-align:center;border:1px solid #000000; background-color: {{($val['sunday_day_hours'] * $val['sunday_day_rate'] - $val['sunday_day_hours'] * $val['award_rate']['award_sunday_day_rate'] < 0 ? 'red' : 'green')}};">
  ${{number_format(($val['sunday_day_hours'] * $val['sunday_day_rate'] - $val['sunday_day_hours'] * $val['award_rate']['award_sunday_day_rate']), 2)}}</td> -->
</tr>
<?php } ?>
  <?php
  if ($val['sunday_night_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;">Over Time Sunday Night</td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['sunday_night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['sunday_night_hours'] * $val['sunday_night_rate'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format(($val['sunday_night_hours'] * $val['award_rate']['award_sunday_night_rate']), 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['sunday_night_hours'] * $val['award_rate']['award_sunday_night_rate']) - ($val['sunday_night_hours'] * $val['sunday_night_rate']) < 0 ? 'red' : 'green')}};">
  ${{number_format(($val['sunday_night_hours'] * $val['award_rate']['award_sunday_night_rate']) - ($val['sunday_night_hours'] * $val['sunday_night_rate']), 2)}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['sunday_night_hours'] * $val['award_rate']['award_sunday_night_rate'], 2)}}</td> -->
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: {{($val['sunday_night_hours'] * $val['sunday_night_rate'] - $val['sunday_night_hours'] * $val['award_rate']['award_sunday_night_rate'] < 0 ? 'red' : 'green')}};">
  ${{number_format(($val['sunday_night_hours'] * $val['sunday_night_rate'] - $val['sunday_night_hours'] * $val['award_rate']['award_sunday_night_rate']), 2)}}</td> -->
</tr>
<?php } ?>
  <?php
  if ($val['ph_day_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['ph_day_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['ph_day_hours'] * $val['ph_day_rate'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format(($val['ph_day_hours'] * $val['award_rate']['award_ph_day_rate']), 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['ph_day_hours'] * $val['award_rate']['award_ph_day_rate']) - ($val['ph_day_hours'] * $val['ph_day_rate']) < 0 ? 'red' : 'green')}};">
  ${{number_format((($val['ph_day_hours'] * $val['award_rate']['award_ph_day_rate']) - ($val['ph_day_hours'] * $val['ph_day_rate'])), 2)}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['ph_day_hours'] * $val['award_rate']['award_ph_day_rate'], 2)}}</td> -->
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: {{($val['ph_day_hours'] * $val['ph_day_rate'] - $val['ph_day_hours'] * $val['award_rate']['award_ph_day_rate'] < 0 ? 'red' : 'green')}};">
  ${{number_format(($val['ph_day_hours'] * $val['ph_day_rate'] - $val['ph_day_hours'] * $val['award_rate']['award_ph_day_rate']), 2)}}</td> -->
</tr>
<?php } ?>
  <?php
  if ($val['ph_night_hours'] > 0) {
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
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;"></td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['ph_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000; border:1px solid #000000;">${{$val['ph_night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} - {{$val['site_description']}} {{$val['temp_start']}} - {{$val['temp_end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['ph_night_hours'] * $val['ph_night_rate'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format(($val['ph_night_hours'] * $val['award_rate']['award_ph_night_rate']), 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: {{(($val['ph_night_hours'] * $val['award_rate']['award_ph_night_rate']) - ($val['ph_night_hours'] * $val['ph_night_rate']) < 0 ? 'red' : 'green')}};">
  ${{number_format((($val['ph_night_hours'] * $val['award_rate']['award_ph_night_rate'])-($val['ph_night_hours'] * $val['ph_night_rate'])), 2)}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">${{number_format($val['ph_night_hours'] * $val['award_rate']['award_ph_night_rate'], 2)}}</td> -->
</tr>
<?php } ?>


@endforeach


</tbody>

</table>